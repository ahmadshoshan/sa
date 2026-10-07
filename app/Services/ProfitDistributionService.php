<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerDistribution;
use App\Models\PartnerDistributionItem;
use App\Models\Payment;
use App\Models\PeriodClosing;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfitDistributionService
{
    public function __construct(
        protected CompanyClosingService $closing,
        protected JournalPostingService $journal
    ) {}

    public function createDraft(array $data): PartnerDistribution
    {
        return DB::transaction(function () use ($data) {

            $from = $data['period_from'];
            $to = $data['period_to'];

            $netProfit = $this->closing->getNetProfit($from, $to);

            $reserveAmount = round((float) ($data['reserve_amount'] ?? 0), 2);

            if ($reserveAmount < 0) {
                throw new Exception('الاحتياطي لا يمكن أن يكون سالبًا.');
            }

            $distributeAmount = array_key_exists('distribute_amount', $data) && $data['distribute_amount'] !== null
                ? round((float) $data['distribute_amount'], 2)
                : max(round($netProfit - $reserveAmount, 2), 0);

            if ($distributeAmount < 0) {
                throw new Exception('مبلغ التوزيع لا يمكن أن يكون سالبًا.');
            }

            $distribution = PartnerDistribution::create([
                'distribution_no' => $this->generateDistributionNumber(),
                'period_type' => $data['period_type'] ?? 'custom',
                'period_from' => $from,
                'period_to' => $to,
                'net_profit' => $netProfit,
                'reserve_amount' => $reserveAmount,
                'distribute_amount' => $distributeAmount,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $this->generateItems($distribution);

            return $distribution;
        });
    }

    public function approve(PartnerDistribution $distribution): PartnerDistribution
    {
        return DB::transaction(function () use ($distribution) {

            if ($distribution->status !== 'draft') {
                throw new Exception('لا يمكن اعتماد توزيع غير مسودة.');
            }

            $from = $distribution->period_from->toDateString();
            $to = $distribution->period_to->toDateString();

            $closing = PeriodClosing::where('period_from', $from)
                ->where('period_to', $to)
                ->first();

            if ($closing) {
                $netProfit = (float) $closing->net_profit;
            } else {
                $netProfit = $this->closing->closePeriod($from, $to);
            }

            $distribution->net_profit = $netProfit;

            $available = round($netProfit - (float) $distribution->reserve_amount, 2);

            if ((float) $distribution->distribute_amount > $available + 0.01) {
                throw new Exception('مبلغ التوزيع أكبر من الربح القابل للتوزيع بعد خصم الاحتياطي.');
            }

            $totalAllocation = round((float) $distribution->reserve_amount + (float) $distribution->distribute_amount, 2);

            if ($totalAllocation > 0) {
                $lines = [
                    [
                        'account_code' => '3103',
                        'debit' => $totalAllocation,
                        'credit' => 0,
                    ],
                ];

                if ((float) $distribution->reserve_amount > 0) {
                    $lines[] = [
                        'account_code' => '3104',
                        'debit' => 0,
                        'credit' => (float) $distribution->reserve_amount,
                    ];
                }

                if ((float) $distribution->distribute_amount > 0) {
                    $lines[] = [
                        'account_code' => '3201',
                        'debit' => 0,
                        'credit' => (float) $distribution->distribute_amount,
                    ];
                }

                $this->journal->createJournal(
                    now()->toDateString(),
                    'اعتماد توزيع الأرباح رقم ' . $distribution->distribution_no,
                    'distribution',
                    $distribution->id,
                    $lines
                );
            }

            $distribution->status = 'posted';
            $distribution->approved_at = now();
            $distribution->save();

            return $distribution;
        });
    }

    public function payItem(PartnerDistributionItem $item, float $amount, string $paymentMethod, string $paymentDate, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($item, $amount, $paymentMethod, $paymentDate, $notes) {

            $item = PartnerDistributionItem::lockForUpdate()->find($item->id);

            if (!$item) {
                throw new Exception('بند التوزيع غير موجود.');
            }

            if ($item->distribution->status !== 'posted') {
                throw new Exception('لا يمكن السداد قبل اعتماد التوزيع.');
            }

            $amount = round($amount, 2);

            $remaining = round((float) $item->final_amount - (float) $item->paid_amount, 2);

            if ($amount <= 0) {
                throw new Exception('المبلغ يجب أن يكون أكبر من صفر.');
            }

            if ($amount > $remaining) {
                throw new Exception('المبلغ أكبر من المتبقي للشريك.');
            }

            return Payment::create([
                'payment_no' => $this->generatePaymentNumber(),
                'type' => 'payment',
                'customer_id' => null,
                'supplier_id' => null,
                'invoice_id' => null,
                'installment_id' => null,
                'expense_id' => null,
                'account_id' => null,
                'distribution_item_id' => $item->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'notes' => $notes ?: 'سداد نصيب الشريك ' . ($item->partner?->name ?? ''),
                'user_id' =>  Auth::id(),
            ]);
        });
    }

    private function generateItems(PartnerDistribution $distribution): void
    {
        $partners = Partner::where('is_active', true)
            ->where('profit_share', '>', 0)
            ->orderBy('id')
            ->get();

        if ($partners->count() === 0) {
            throw new Exception('لا يوجد شركاء نشطون لديهم نسبة أرباح.');
        }

        $totalShare = (float) $partners->sum('profit_share');

        if ($totalShare <= 0) {
            throw new Exception('مجموع نسب الأرباح يجب أن يكون أكبر من صفر.');
        }

        $distributeAmount = (float) $distribution->distribute_amount;
        $allocated = 0;

        foreach ($partners as $index => $partner) {
            $share = (float) $partner->profit_share / $totalShare;

            if ($index === $partners->count() - 1) {
                $baseAmount = round($distributeAmount - $allocated, 2);
            } else {
                $baseAmount = round($distributeAmount * $share, 2);
            }

            $allocated += $baseAmount;

            PartnerDistributionItem::create([
                'distribution_id' => $distribution->id,
                'partner_id' => $partner->id,
                'profit_share' => $partner->profit_share,
                'base_amount' => $baseAmount,
                'additional_amount' => 0,
                'deduction_amount' => 0,
                'final_amount' => $baseAmount,
                'paid_amount' => 0,
                'remaining_amount' => $baseAmount,
                'status' => 'pending',
            ]);
        }
    }

    private function generateDistributionNumber(): string
    {
        $lastId = PartnerDistribution::max('id') + 1;

        return 'PD-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentNumber(): string
    {
        $lastId = Payment::max('id') + 1;

        return 'PAY-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}