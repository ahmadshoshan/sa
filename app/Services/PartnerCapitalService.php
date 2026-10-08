<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerCapital;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerCapitalService
{
    public function __construct(
        protected JournalPostingService $journal
    ) {}

    public function addCapital(Partner $partner, array $data): PartnerCapital
    {
        return DB::transaction(function () use ($partner, $data) {

            $amount = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw new Exception('قيمة رأس المال يجب أن تكون أكبر من صفر.');
            }

            $isCashCredit = $data['contribution_type'] === 'cash' && ($data['payment_method'] ?? 'cash') === 'credit';

            $capital = PartnerCapital::create([
                'partner_id' => $partner->id,
                'contribution_date' => $data['contribution_date'],
                'contribution_type' => $data['contribution_type'],
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? null,
                'status' => $isCashCredit ? 'pending' : 'paid',
                'description' => $data['description'] ?? null,
                'user_id' => Auth::id(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            if ($capital->status === 'pending') {
                return $capital;
            }

            if ($data['contribution_type'] === 'cash') {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('receipt'),
                    'type' => 'receipt',
                    'customer_id' => null,
                    'supplier_id' => null,
                    'invoice_id' => null,
                    'installment_id' => null,
                    'expense_id' => null,
                    'account_id' => $this->journal->accountIdByCode('3101'),
                    'amount' => $amount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'payment_date' => $data['contribution_date'],
                    'notes' => 'مساهمة رأس مال من الشريك '.$partner->name,
                    'user_id' => Auth::id(),
                ]);
            } else {
                $debitAccountCode = $data['contribution_type'] === 'inventory' ? '1201' : '1501';

                $lines = [
                    ['account_code' => $debitAccountCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => '3101', 'debit' => 0, 'credit' => $amount],
                ];

                $this->journal->createJournal(
                    $capital->contribution_date,
                    'مساهمة رأس مال عينية من الشريك '.$partner->name,
                    'partner_capital',
                    $capital->id,
                    $lines
                );
            }

            return $capital;
        });
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;

        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}
