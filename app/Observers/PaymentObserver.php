<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\JournalEntry;
use App\Services\PaymentAccountingService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    protected PaymentAccountingService $accountingService;

    public function __construct(PaymentAccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function created(Payment $payment): void
    {
        try {
            // إنشاء القيد المحاسبي
            $this->accountingService->postPayment($payment);

            // تحديث رصيد العميل/المورد
            $this->updatePartyBalance($payment);

            // // تحديث الفاتورة المرتبطة إن وجدت
            // if ($payment->invoice_id) {
            //     $this->updateInvoicePaidAmount($payment);
            // }
        } catch (\Throwable $e) {
            Log::error('PaymentObserver Error: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function deleted(Payment $payment): void
    {
        try {
            // حذف القيد المحاسبي المرتبط
            JournalEntry::where('ref_type', 'payment')
                ->where('ref_id', $payment->id)
                ->delete();

            // عكس تحديث الرصيد
            $this->reversePartyBalance($payment);

            // عكس تحديث الفاتورة
            if ($payment->invoice_id) {
                $this->reverseInvoicePaidAmount($payment);
            }
        } catch (\Throwable $e) {
            Log::error('PaymentObserver Delete Error: ' . $e->getMessage());
        }
    }

    /**
     * تحديث رصيد العميل/المورد
     * 
     * منطق الأرصدة:
     * - current_balance موجب = العميل/المورد مدين لنا (علينا أن نأخذ منه)
     * - current_balance سالب = العميل/المورد دائن لنا (علينا أن ندفع له)
     * 
     * منطق السندات:
     * 1. receipt + customer_id = العميل دفع لنا (الرصيد -= amount)
     * 2. payment + customer_id = نحن دفعنا للعميل (الرصيد += amount) [رد مستحقات]
     * 3. payment + supplier_id = نحن دفعنا للمورد (الرصيد -= amount)
     * 4. receipt + supplier_id = استلمنا من المورد (الرصيد += amount) [مرتجع]
     */
    protected function updatePartyBalance(Payment $payment): void
    {
        $amount = (float) $payment->amount;
        if ($amount <= 0) return;

        // ============================================
        // حالة 1: سند قبض من عميل (العميل دفع لنا)
        // ============================================
        if ($payment->customer_id && $payment->type === 'receipt') {
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                // الرصيد ينقص (العميل دفع جزءاً مما عليه)
                // $customer->current_balance = (float) $customer->current_balance - $amount;
                $customer->save();
                
                Log::info("Customer balance updated (receipt): {$customer->name}, -{$amount}, new: {$customer->current_balance}");
            }
        }

        // ============================================
        // حالة 2: سند صرف للعميل (نحن ندفع له - رد مستحقات)
        // ============================================
        elseif ($payment->customer_id && $payment->type === 'payment') {
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                // الرصيد يرتفع (نحن رددنا له جزءاً مما لنا عليه)
                // مثال: كان -5000 (نحن مدينون له)، دفعنا له 3000، أصبح -2000
                // $customer->current_balance = (float) $customer->current_balance + $amount;
                $customer->save();
                
                Log::info("Customer balance updated (refund): {$customer->name}, +{$amount}, new: {$customer->current_balance}");
            }
        }

        // ============================================
        // حالة 3: سند صرف للمورد (نحن ندفع له)
        // ============================================
        elseif ($payment->supplier_id && $payment->type === 'payment') {
            $supplier = Supplier::find($payment->supplier_id);
            if ($supplier) {
                // الرصيد ينقص (دفعنا جزءاً مما علينا له)
                // $supplier->current_balance = (float) $supplier->current_balance - $amount;
                $supplier->save();
                
                Log::info("Supplier balance updated (payment): {$supplier->name}, -{$amount}, new: {$supplier->current_balance}");
            }
        }

        // ============================================
        // حالة 4: سند قبض من المورد (استلمنا منه - مرتجع)
        // ============================================
        elseif ($payment->supplier_id && $payment->type === 'receipt') {
            $supplier = Supplier::find($payment->supplier_id);
            if ($supplier) {
                // الرصيد يرتفع (استلمنا منه جزءاً من المرتجع)
                // $supplier->current_balance = (float) $supplier->current_balance + $amount;
                $supplier->save();
                
                Log::info("Supplier balance updated (receipt): {$supplier->name}, +{$amount}, new: {$supplier->current_balance}");
            }
        }
    }

    /**
     * عكس تحديث الرصيد عند الحذف
     */
    protected function reversePartyBalance(Payment $payment): void
    {
        $amount = (float) $payment->amount;
        if ($amount <= 0) return;

        if ($payment->customer_id && $payment->type === 'receipt') {
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                // $customer->current_balance = (float) $customer->current_balance + $amount;
                $customer->save();
            }
        } elseif ($payment->customer_id && $payment->type === 'payment') {
            $customer = Customer::find($payment->customer_id);
            if ($customer) {
                // $customer->current_balance = (float) $customer->current_balance - $amount;
                $customer->save();
            }
        } elseif ($payment->supplier_id && $payment->type === 'payment') {
            $supplier = Supplier::find($payment->supplier_id);
            if ($supplier) {
                // $supplier->current_balance = (float) $supplier->current_balance + $amount;
                $supplier->save();
            }
        } elseif ($payment->supplier_id && $payment->type === 'receipt') {
            $supplier = Supplier::find($payment->supplier_id);
            if ($supplier) {
                // $supplier->current_balance = (float) $supplier->current_balance - $amount;
                $supplier->save();
            }
        }
    }

    /**
     * تحديث المبلغ المدفوع في الفاتورة
     */
    protected function updateInvoicePaidAmount(Payment $payment): void
    {
        $invoice = \App\Models\Invoice::find($payment->invoice_id);
        if (!$invoice) return;

        if ($payment->type === 'receipt' && $payment->customer_id) {
            // قبض من عميل يخصم من فاتورة بيع
            if ($invoice->type === 'sale') {
                $invoice->paid_amount = (float) $invoice->paid_amount + $payment->amount;
                $invoice->remaining_amount = max(0, (float) $invoice->total - (float) $invoice->paid_amount);
                $invoice->save();
            }
        } elseif ($payment->type === 'payment' && $payment->supplier_id) {
            // دفع لمورد يخصم من فاتورة شراء
            if ($invoice->type === 'purchase') {
                $invoice->paid_amount = (float) $invoice->paid_amount + $payment->amount;
                $invoice->remaining_amount = max(0, (float) $invoice->total - (float) $invoice->paid_amount);
                $invoice->save();
            }
        }
    }

    protected function reverseInvoicePaidAmount(Payment $payment): void
    {
        $invoice = \App\Models\Invoice::find($payment->invoice_id);
        if (!$invoice) return;

        if ($payment->type === 'receipt' && $payment->customer_id && $invoice->type === 'sale') {
            $invoice->paid_amount = max(0, (float) $invoice->paid_amount - $payment->amount);
            $invoice->remaining_amount = (float) $invoice->total - (float) $invoice->paid_amount;
            $invoice->save();
        } elseif ($payment->type === 'payment' && $payment->supplier_id && $invoice->type === 'purchase') {
            $invoice->paid_amount = max(0, (float) $invoice->paid_amount - $payment->amount);
            $invoice->remaining_amount = (float) $invoice->total - (float) $invoice->paid_amount;
            $invoice->save();
        }
    }
}