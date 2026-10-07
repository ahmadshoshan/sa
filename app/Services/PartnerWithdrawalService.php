<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerWithdrawal;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerWithdrawalService
{
    public function __construct(
        protected JournalPostingService $journal
    ) {}

    public function addWithdrawal(Partner $partner, array $data): PartnerWithdrawal
    {
        return DB::transaction(function () use ($partner, $data) {

            $amount = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw new Exception('قيمة المسحوبات يجب أن تكون أكبر من صفر.');
            }

            $withdrawal = PartnerWithdrawal::create([
                'partner_id' => $partner->id,
                'withdrawal_date' => $data['withdrawal_date'],
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
            ]);

            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber('payment'),
                'type' => 'payment',
                'customer_id' => null,
                'supplier_id' => null,
                'invoice_id' => null,
                'installment_id' => null,
                'expense_id' => null,
                'account_id' => $this->journal->accountIdByCode('3102'),
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['withdrawal_date'],
                'notes' => 'مسحوبات الشريك ' . $partner->name,
                'user_id' =>  Auth::id(),
            ]);

            $withdrawal->payment_id = $payment->id;
            $withdrawal->save();

            return $withdrawal;
        });
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}