<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PartnerDistributionItem;
use App\Models\Payment;
use App\Models\Supplier;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createManual(array $data): Payment
    {
        return DB::transaction(function () use ($data) {

            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new Exception('المبلغ يجب أن يكون أكبر من صفر.');
            }

            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber($data['type']),
                'type' => $data['type'],
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'invoice_id' => null,
                'installment_id' => null,
                'expense_id' => null,
                'account_id' => null,
                'distribution_item_id' => null,
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? 'لا يوجد',
                'user_id' => Auth::id(),
                'idempotency_key' => $data['idempotency_key'] ?? null,
            ]);

            if (! empty($data['customer_id'])) {
                $customer = Customer::lockForUpdate()->find($data['customer_id']);

                if (! $customer) {
                    throw new Exception('العميل غير موجود.');
                }

                if ($data['type'] === 'receipt') {
                    $customer->current_balance = (float) $customer->current_balance - $amount;
                } else {
                    $customer->current_balance = (float) $customer->current_balance + $amount;
                }

                $customer->save();
            }

            if (! empty($data['supplier_id'])) {
                $supplier = Supplier::lockForUpdate()->find($data['supplier_id']);

                if (! $supplier) {
                    throw new Exception('المورد غير موجود.');
                }

                if ($data['type'] === 'payment') {
                    $supplier->current_balance = (float) $supplier->current_balance - $amount;
                } else {
                    $supplier->current_balance = (float) $supplier->current_balance + $amount;
                }

                $supplier->save();
            }

            return $payment;
        });
    }

    public function syncInvoiceTotals(int $invoiceId): void
    {
        $invoice = Invoice::find($invoiceId);

        if (! $invoice) {
            return;
        }

        $paid = (float) Payment::where('invoice_id', $invoiceId)->sum('amount');
        $paid = min((float) $invoice->total, $paid);

        $invoice->update([
            'paid_amount' => $paid,
            'remaining_amount' => (float) $invoice->total - $paid,
        ]);
    }

    public function syncExpenseTotals(int $expenseId): void
    {
        $expense = Expense::find($expenseId);

        if (! $expense) {
            return;
        }

        $paid = (float) Payment::where('expense_id', $expenseId)->sum('amount');
        $paid = min((float) $expense->total, $paid);

        $status = 'unpaid';

        if ($paid >= (float) $expense->total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }

        $expense->update([
            'paid_amount' => $paid,
            'remaining_amount' => (float) $expense->total - $paid,
            'payment_status' => $status,
        ]);
    }

    public function syncDistributionItemTotals(int $itemId): void
    {
        $item = PartnerDistributionItem::find($itemId);

        if (! $item) {
            return;
        }

        $paid = (float) Payment::where('distribution_item_id', $itemId)->sum('amount');
        $paid = min((float) $item->final_amount, $paid);

        $status = 'pending';

        if ($paid >= (float) $item->final_amount) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }

        $item->update([
            'paid_amount' => $paid,
            'remaining_amount' => (float) $item->final_amount - $paid,
            'status' => $status,
        ]);
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;

        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}
