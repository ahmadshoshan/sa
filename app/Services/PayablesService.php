<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class PayablesService
{
    /**
     * دفع مصروف
     * ⚠️ لا ينشئ قيود محاسبية - يتم ذلك تلقائياً من PaymentObserver
     */
    public function payExpense(int $expenseId, float $amount, string $method, int $accountId, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($expenseId, $amount, $method, $accountId, $notes) {
            $expense = Expense::with('category')->findOrFail($expenseId);

            if ($amount <= 0) throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
            if ($amount > $expense->remaining_amount) {
                throw new Exception('المبلغ أكبر من المتبقي. المتبقي: ' . number_format($expense->remaining_amount, 2));
            }

            $account = Account::find($accountId);
            if (!$account) throw new Exception('الحساب غير موجود');
            if (!in_array($account->code, ['1001', '1002', '1003'])) {
                throw new Exception('يجب اختيار حساب نقدي');
            }

            $lastId = Payment::max('id') ?? 0;
            $payment = Payment::create([
                'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'payment',
                'customer_id' => null,
                'supplier_id' => null,
                'invoice_id' => null,
                'expense_id' => $expense->id,
                'account_id' => $accountId,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $notes ?: 'دفع مصروف رقم ' . $expense->expense_no,
                'user_id' =>  Auth::id(),
            ]);

            $newPaid = $expense->paid_amount + $amount;
            $expense->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => $expense->total - $newPaid,
                'payment_status' => $newPaid >= $expense->total ? 'paid' : 'partial',
            ]);

            return $payment;
        });
    }

    /**
     * دفع فاتورة مشتريات مستحقة
     * ⚠️ لا ينشئ قيود محاسبية - يتم ذلك تلقائياً من PaymentObserver
     */
    public function payInvoice(int $invoiceId, float $amount, string $method, int $accountId, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($invoiceId, $amount, $method, $accountId, $notes) {
            $invoice = Invoice::with('supplier')->findOrFail($invoiceId);

            if ($invoice->type !== 'purchase') {
                throw new Exception('هذه ليست فاتورة شراء');
            }

            if ($amount <= 0) throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
            if ($amount > $invoice->remaining_amount) {
                throw new Exception('المبلغ أكبر من المتبقي. المتبقي: ' . number_format($invoice->remaining_amount, 2));
            }

            $account = Account::find($accountId);
            if (!$account) throw new Exception('الحساب غير موجود');
            if (!in_array($account->code, ['1001', '1002', '1003'])) {
                throw new Exception('يجب اختيار حساب نقدي');
            }

            $lastId = Payment::max('id') ?? 0;
            $payment = Payment::create([
                'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'payment',
                'customer_id' => null,
                'supplier_id' => $invoice->supplier_id,
                'invoice_id' => $invoice->id,
                'expense_id' => null,
                'account_id' => $accountId,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $notes ?: 'دفع فاتورة شراء رقم ' . $invoice->invoice_no,
                'user_id' =>  Auth::id(),
            ]);

            // إعادة حساب مدفوعات الفاتورة من السندات الفعلية
            $actualPaid = Payment::where('invoice_id', $invoice->id)
                ->where('type', 'payment')
                ->sum('amount');

            $invoice->update([
                'paid_amount' => $actualPaid,
                'remaining_amount' => $invoice->total - $actualPaid,
            ]);

            // إعادة حساب رصيد المورد
            if ($invoice->supplier) {
                $totalInvoices = Invoice::where('type', 'purchase')
                    ->where('supplier_id', $invoice->supplier_id)
                    ->sum('total');

                $totalPaid = Payment::where('supplier_id', $invoice->supplier_id)
                    ->where('type', 'payment')
                    ->sum('amount');

                $totalReturns = Invoice::where('type', 'purchase_return')
                    ->where('supplier_id', $invoice->supplier_id)
                    ->sum('total');

                $invoice->supplier->update([
                    'current_balance' => ($totalInvoices - $totalReturns) - $totalPaid + $invoice->supplier->opening_balance,
                ]);
            }

            return $payment;
        });
    }

    /**
     * دفع مرتب
     */
    public function paySalary(string $employeeName, float $amount, string $method, int $accountId, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($employeeName, $amount, $method, $accountId, $notes) {
            if ($amount <= 0) throw new Exception('المبلغ يجب أن يكون أكبر من صفر');

            $account = Account::find($accountId);
            if (!$account) throw new Exception('الحساب غير موجود');

            $lastId = Payment::max('id') ?? 0;
            return Payment::create([
                'payment_no' => 'PAY-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'payment',
                'account_id' => $accountId,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $notes ?: 'دفع مرتب ' . $employeeName,
                'user_id' =>  Auth::id(),
            ]);
        });
    }

    public function getPayableExpenses()
    {
        return Expense::with('category')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderBy('expense_date')->get();
    }

    public function getPayableInvoices()
    {
        return Invoice::with('supplier')
            ->where('type', 'purchase')
            ->where('remaining_amount', '>', 0)
            ->orderBy('invoice_date')->get();
    }
}