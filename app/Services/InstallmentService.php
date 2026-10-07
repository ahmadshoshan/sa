<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceInstallment;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallmentService
{
    public function generatePlan(Invoice $invoice, int $count, int $intervalDays, string $firstDate): void
    {
        if ($invoice->type !== 'sale') {
            throw new Exception('الجدولة متاحة فقط لفواتير البيع.');
        }

        $remaining = (float) $invoice->remaining_amount;

        if ($remaining <= 0) {
            throw new Exception('لا يوجد مبلغ متبقٍ على الفاتورة لجدولته.');
        }

        if ($count < 1 || $count > 120) {
            throw new Exception('عدد الأقساط يجب أن يكون بين 1 و 120.');
        }

        if ($intervalDays < 0 || $intervalDays > 365) {
            throw new Exception('الفاصل الزمني غير صالح.');
        }

        DB::transaction(function () use ($invoice, $count, $intervalDays, $firstDate, $remaining) {

            $existing = InvoiceInstallment::where('invoice_id', $invoice->id)
                ->lockForUpdate()
                ->get();

            if ((float) $existing->sum('paid_amount') > 0) {
                throw new Exception('لا يمكن إعادة الجدولة بعد وجود دفعات على الأقساط.');
            }

            InvoiceInstallment::where('invoice_id', $invoice->id)->delete();

            $perInstallment = round($remaining / $count, 2);
            $allocated = 0;
            $date = Carbon::parse($firstDate);

            for ($i = 1; $i <= $count; $i++) {
                if ($i === $count) {
                    $amount = round($remaining - $allocated, 2);
                } else {
                    $amount = $perInstallment;
                }

                $allocated += $amount;

                InvoiceInstallment::create([
                    'invoice_id' => $invoice->id,
                    'due_date' => $date->copy()->addDays(($i - 1) * $intervalDays)->toDateString(),
                    'amount' => $amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
            }
        });
    }

    public function payInstallment(
        InvoiceInstallment $installment,
        float $amount,
        string $paymentMethod,
        string $paymentDate,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($installment, $amount, $paymentMethod, $paymentDate, $notes) {

            $installment = InvoiceInstallment::lockForUpdate()->find($installment->id);

            if (!$installment) {
                throw new Exception('القسط غير موجود.');
            }

            $invoice = Invoice::lockForUpdate()->find($installment->invoice_id);

            if (!$invoice) {
                throw new Exception('الفاتورة غير موجودة.');
            }

            $amount = round($amount, 2);

            $installmentRemaining = (float) $installment->amount - (float) $installment->paid_amount;

            if ($amount <= 0) {
                throw new Exception('المبلغ يجب أن يكون أكبر من صفر.');
            }

            if ($amount > $installmentRemaining) {
                throw new Exception('المبلغ أكبر من المتبقي على القسط.');
            }

            if ($amount > (float) $invoice->remaining_amount) {
                throw new Exception('المبلغ أكبر من المتبقي على الفاتورة.');
            }

            $payment = Payment::create([
                'payment_no' => $this->generatePaymentNumber('receipt'),
                'type' => 'receipt',
                'customer_id' => $invoice->customer_id,
                'supplier_id' => null,
                'invoice_id' => $invoice->id,
                'installment_id' => $installment->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'notes' => $notes ?: 'دفعة قسط رقم ' . $installment->id,
                'user_id' =>  Auth::id(),
            ]);

            $installment->paid_amount = (float) $installment->paid_amount + $amount;

            $installment->status = (float) $installment->paid_amount >= (float) $installment->amount
                ? 'paid'
                : 'partial';

            $installment->save();

            return $payment;
        });
    }

    private function generatePaymentNumber(string $type): string
    {
        $prefix = $type === 'receipt' ? 'REC' : 'PAY';
        $lastId = Payment::max('id') + 1;

        return $prefix . '-' . now()->format('Ymd') . '-' . str_pad((string) $lastId, 6, '0', STR_PAD_LEFT);
    }
}