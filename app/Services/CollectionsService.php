<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class CollectionsService
{
    /**
     * تحصيل مستحق من عميل
     * ⚠️ لا ينشئ قيود محاسبية - يتم ذلك تلقائياً من PaymentObserver
     */
    public function collectFromCustomer(int $invoiceId, float $amount, string $method, int $accountId, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($invoiceId, $amount, $method, $accountId, $notes) {
            $invoice = Invoice::with('customer')->findOrFail($invoiceId);

            if ($invoice->type !== 'sale') {
                throw new Exception('هذه ليست فاتورة بيع');
            }

            if ($amount <= 0) {
                throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
            }

            if ($amount > $invoice->remaining_amount) {
                throw new Exception('المبلغ أكبر من المتبقي. المتبقي: ' . number_format($invoice->remaining_amount, 2));
            }

            $account = Account::find($accountId);
            if (!$account) {
                throw new Exception('الحساب غير موجود');
            }

            // التحقق من نوع الحساب - يجب أن يكون حساب نقدي
            if (!in_array($account->code, ['1001', '1002', '1003'])) {
                throw new Exception('يجب اختيار حساب نقدي (صندوق/بنك/كاش)');
            }

            // إنشاء السند فقط - القيد المحاسبي سيُنشأ تلقائياً من PaymentObserver
            $lastId = Payment::max('id') ?? 0;
            $payment = Payment::create([
                'payment_no' => 'REC-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 6, '0', STR_PAD_LEFT),
                'type' => 'receipt',
                'customer_id' => $invoice->customer_id,
                'supplier_id' => null,
                'invoice_id' => $invoice->id,
                'expense_id' => null,
                'account_id' => $accountId,
                'amount' => $amount,
                'payment_method' => $method,
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $notes ?: 'تحصيل من فاتورة بيع رقم ' . $invoice->invoice_no,
                'user_id' =>  Auth::id(),
            ]);

            // إعادة حساب مدفوعات الفاتورة من السندات الفعلية
            $actualPaid = Payment::where('invoice_id', $invoice->id)
                ->where('type', 'receipt')
                ->sum('amount');

            $invoice->update([
                'paid_amount' => $actualPaid,
                'remaining_amount' => $invoice->total - $actualPaid,
            ]);

            // إعادة حساب رصيد العميل من الفواتير والسندات الفعلية
            if ($invoice->customer) {
                $totalInvoices = Invoice::where('type', 'sale')
                    ->where('customer_id', $invoice->customer_id)
                    ->sum('total');

                $totalPaid = Payment::where('customer_id', $invoice->customer_id)
                    ->where('type', 'receipt')
                    ->sum('amount');

                $totalReturns = Invoice::where('type', 'sale_return')
                    ->where('customer_id', $invoice->customer_id)
                    ->sum('total');

                $invoice->customer->update([
                    'current_balance' => ($totalInvoices - $totalReturns) - $totalPaid + $invoice->customer->opening_balance,
                ]);
            }

            return $payment;
        });
    }
}