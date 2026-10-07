<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceInstallment;
use App\Services\InstallmentService;
use Illuminate\Http\Request;
use Throwable;

class InstallmentController extends Controller
{
    public function show(Invoice $invoice)
    {
        if ($invoice->type !== 'sale') {
            abort(404);
        }

        $invoice->load(['customer', 'installments']);

        return view('sales.installments', compact('invoice'));
    }

    public function generate(Request $request, Invoice $invoice, InstallmentService $installmentService)
    {
        if ($invoice->type !== 'sale') {
            abort(404);
        }

        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:120'],
            'interval_days' => ['required', 'integer', 'min:0', 'max:365'],
            'first_date' => ['required', 'date'],
        ]);

        try {
            $installmentService->generatePlan(
                $invoice,
                (int) $validated['count'],
                (int) $validated['interval_days'],
                $validated['first_date']
            );

            return redirect()
                ->route('sales.installments', $invoice)
                ->with('success', 'تم إنشاء خطة التقسيط بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function pay(Request $request, InvoiceInstallment $installment, InstallmentService $installmentService)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $installmentService->payInstallment(
                $installment,
                (float) $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('sales.installments', $installment->invoice_id)
                ->with('success', 'تم تسجيل دفعة القسط بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}