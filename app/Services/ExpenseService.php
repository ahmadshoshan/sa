<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        protected JournalPostingService $journal
    ) {}

    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {

            $category = ExpenseCategory::find($data['category_id']);

            if (!$category) {
                throw new Exception('تصنيف المصروف غير موجود.');
            }

            $amount = round((float) $data['amount'], 2);
            $taxAmount = round((float) ($data['tax_amount'] ?? 0), 2);
            $total = round($amount + $taxAmount, 2);
            $paymentMethod = $data['payment_method'] ?? 'cash';
            $paidAmount = $paymentMethod === 'credit' ? 0 : $total;
            $remainingAmount = $total - $paidAmount;

            if ($total <= 0) {
                throw new Exception('قيمة المصروف يجب أن تكون أكبر من صفر.');
            }

            $expense = Expense::create([
                'expense_no' => $this->generateExpenseNumber(),
                'expense_date' => $data['expense_date'],
                'category_id' => $category->id,
                'description' => $data['description'] ?? null,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => $remainingAmount > 0 ? 'unpaid' : 'paid',
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'partner_id' => $data['partner_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'user_id' =>  Auth::id(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            $expenseAccountCode = $category->account?->code ?? '5312';

            $lines = [];

            if ($taxAmount > 0) {
                $lines[] = ['account_code' => $expenseAccountCode, 'debit' => $amount, 'credit' => 0];
                $lines[] = ['account_code' => '1301', 'debit' => $taxAmount, 'credit' => 0];
                $lines[] = ['account_code' => '2301', 'debit' => 0, 'credit' => $total];
            } else {
                $lines[] = ['account_code' => $expenseAccountCode, 'debit' => $total, 'credit' => 0];
                $lines[] = ['account_code' => '2301', 'debit' => 0, 'credit' => $total];
            }

            $this->journal->createJournal(
                $expense->expense_date,
                'قيد مصروف رقم ' . $expense->expense_no,
                'expense',
                $expense->id,
                $lines
            );

            if ($paymentMethod !== 'credit') {
                Payment::create([
                    'payment_no' => $this->generatePaymentNumber('payment'),
                    'type' => 'payment',
                    'customer_id' => null,
                    'supplier_id' => null,
                    'invoice_id' => null,
                    'installment_id' => null,
                    'expense_id' => $expense->id,
                    'account_id' => null,
                    'amount' => $total,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $data['expense_date'],
                    'notes' => 'سداد مصروف رقم ' . $expense->expense_no,
                    'user_id' =>  Auth::id(),
                ]);
            }

            return $expense;
        });
    }

    private function generateExpenseNumber(): string
    {
        $lastId = Expense::max('id') + 1;

        return 'EXP-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}