<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Services\CollectionsService;
use Illuminate\Http\Request;

class CollectionsController extends Controller
{
    protected CollectionsService $collectionsService;

    public function __construct(CollectionsService $collectionsService)
    {
        $this->collectionsService = $collectionsService;
    }

    public function index()
    {
        $receivableInvoices = Invoice::with('customer')
            ->where('type', 'sale')
            ->where('remaining_amount', '>', 0)
            ->orderBy('invoice_date')
            ->get();

        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();
        $totalReceivables = $receivableInvoices->sum('remaining_amount');

        return view('collections.index', compact('receivableInvoices', 'cashAccounts', 'totalReceivables'));
    }

    public function collectForm(Invoice $invoice)
    {
        if ($invoice->type !== 'sale') {
            return redirect()->route('collections.index')->with('error', 'هذه ليست فاتورة بيع');
        }

        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();
        return view('collections.collect', compact('invoice', 'cashAccounts'));
    }

    public function collect(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->remaining_amount],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->collectionsService->collectFromCustomer(
                $invoice->id,
                (float) $validated['amount'],
                $validated['payment_method'],
                (int) $validated['account_id'],
                $validated['notes'] ?? null
            );

            return redirect()->route('collections.index')->with('success', 'تم التحصيل بنجاح! رقم السند: ' . $payment->payment_no);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}