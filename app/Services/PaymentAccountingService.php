<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Payment;

class PaymentAccountingService
{
    public function __construct(
        protected JournalPostingService $journal
    ) {}

    public function postPayment(Payment $payment): void
    {
        if ($payment->payment_method === 'credit') {
            return;
        }

        if (JournalEntry::where('ref_type', 'payment')->where('ref_id', $payment->id)->exists()) {
            return;
        }

        $amount = round((float) $payment->amount, 2);

        if ($amount <= 0) {
            return;
        }

        $cashAccountCode = $this->resolveCashAccountCode($payment);

        // =====================================================
        // توزيع أرباح الشركاء
        // =====================================================
        if ($payment->distribution_item_id) {
            if ($payment->type === 'payment') {
                $lines = [
                    ['account_code' => '3201', 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $cashAccountCode, 'debit' => 0, 'credit' => $amount],
                ];
            } else {
                $lines = [
                    ['account_code' => $cashAccountCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => '3201', 'debit' => 0, 'credit' => $amount],
                ];
            }

            $this->journal->createJournal(
                $payment->payment_date,
                ($payment->type === 'payment' ? 'سند صرف نصيب شريك رقم ' : 'سند قبض نصيب شريك رقم ') . $payment->payment_no,
                'payment',
                $payment->id,
                $lines
            );

            return;
        }

        // =====================================================
        // المصروفات
        // مهم جداً:
        // لا نكرر حساب المصروف هنا
        // لأن المصروف تم إثباته عند إنشاء Expense:
        // من حـ/ المصروف
        // إلى حـ/ مصروفات مستحقة 2301
        //
        // وعند السداد الصحيح:
        // من حـ/ مصروفات مستحقة 2301
        // إلى حـ/ الصندوق/البنك/الإنستا
        // =====================================================
        if ($payment->expense_id) {
            if ($payment->type === 'payment') {
                $lines = [
                    ['account_code' => '2301', 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $cashAccountCode, 'debit' => 0, 'credit' => $amount],
                ];
            } else {
                $lines = [
                    ['account_code' => $cashAccountCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => '2301', 'debit' => 0, 'credit' => $amount],
                ];
            }

            $this->journal->createJournal(
                $payment->payment_date,
                ($payment->type === 'payment' ? 'سداد مصروف رقم ' : 'رد مصروف رقم ') . $payment->payment_no,
                'payment',
                $payment->id,
                $lines
            );

            return;
        }

        // =====================================================
        // سند على حساب عام
        // ملاحظة:
        // لو account_id هو نفسه الحساب النقدي، لا ننشئ قيداً عاماً
        // حتى لا يحصل قيد من الحساب لنفسه
        // =====================================================
        if ($payment->account_id && !$payment->invoice_id && !$payment->customer_id && !$payment->supplier_id) {
            $account = Account::find($payment->account_id);

            if (!$account) {
                return;
            }

            if ($account->code === $cashAccountCode) {
                return;
            }

            if ($payment->type === 'payment') {
                $lines = [
                    ['account_code' => $account->code, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $cashAccountCode, 'debit' => 0, 'credit' => $amount],
                ];
            } else {
                $lines = [
                    ['account_code' => $cashAccountCode, 'debit' => $amount, 'credit' => 0],
                    ['account_code' => $account->code, 'debit' => 0, 'credit' => $amount],
                ];
            }

            $this->journal->createJournal(
                $payment->payment_date,
                ($payment->type === 'payment' ? 'سند صرف رقم ' : 'سند قبض رقم ') . $payment->payment_no,
                'payment',
                $payment->id,
                $lines
            );

            return;
        }

        // =====================================================
        // تحصيل / صرف مع العملاء
        // =====================================================
        if ($payment->customer_id) {
            if ($payment->type === 'receipt') {
                $lines = [
                    [
                        'account_code' => $cashAccountCode,
                        'debit' => $amount,
                        'credit' => 0,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => '1101',
                        'debit' => 0,
                        'credit' => $amount,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];
            } else {
                $lines = [
                    [
                        'account_code' => '1101',
                        'debit' => $amount,
                        'credit' => 0,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => $cashAccountCode,
                        'debit' => 0,
                        'credit' => $amount,
                        'customer_id' => $payment->customer_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];
            }

            $this->journal->createJournal(
                $payment->payment_date,
                ($payment->type === 'receipt' ? 'سند قبض رقم ' : 'سند صرف رقم ') . $payment->payment_no,
                'payment',
                $payment->id,
                $lines
            );

            return;
        }

        // =====================================================
        // تحصيل / صرف مع الموردين
        // =====================================================
        if ($payment->supplier_id) {
            if ($payment->type === 'receipt') {
                $lines = [
                    [
                        'account_code' => $cashAccountCode,
                        'debit' => $amount,
                        'credit' => 0,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => '2101',
                        'debit' => 0,
                        'credit' => $amount,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];
            } else {
                $lines = [
                    [
                        'account_code' => '2101',
                        'debit' => $amount,
                        'credit' => 0,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                    [
                        'account_code' => $cashAccountCode,
                        'debit' => 0,
                        'credit' => $amount,
                        'supplier_id' => $payment->supplier_id,
                        'invoice_id' => $payment->invoice_id,
                    ],
                ];
            }

            $this->journal->createJournal(
                $payment->payment_date,
                ($payment->type === 'receipt' ? 'سند قبض رقم ' : 'سند صرف رقم ') . $payment->payment_no,
                'payment',
                $payment->id,
                $lines
            );

            return;
        }
    }

    protected function resolveCashAccountCode(Payment $payment): string
    {
        if ($payment->account_id) {
            $account = Account::find($payment->account_id);

            if ($account && in_array($account->code, ['1001', '1002', '1003'])) {
                return $account->code;
            }
        }

        return match ($payment->payment_method) {
            'cash' => '1001',
            'card', 'insta', 'visa' => '1003',
            'bank_transfer', 'cheque' => '1002',
            default => '1001',
        };
    }
}