<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Account;
use App\Services\InvoiceReturnService;
use Illuminate\Http\Request;

class InvoiceReturnController extends Controller
{
    protected InvoiceReturnService $returnService;

    public function __construct(InvoiceReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * عرض نموذج إرجاع الفاتورة
     */
    public function createFromInvoice(Invoice $invoice)
    {
        if (!in_array($invoice->type, ['sale', 'purchase'])) {
            return redirect()->back()->with('error', 'لا يمكن إرجاع هذه الفاتورة.');
        }

        $invoice->load(['items.product', 'customer', 'supplier']);
        $accounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();

        return view('returns.create_from_invoice', compact('invoice', 'accounts'));
    }

    /**
     * تنفيذ الإرجاع
     */
    public function storeFromInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:invoice_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'settlement_method' => ['required', 'in:cash_refund,carry_forward,offset_invoice'],
            'account_id' => ['nullable', 'exists:accounts,id', 'required_if:settlement_method,cash_refund'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $returnInvoice = $this->returnService->createReturnFromInvoice($invoice, $validated);

            $routeName = $invoice->type === 'purchase' ? 'purchases.index' : 'sales.index';

            return redirect()->route($routeName)
                ->with('success', "تم إنشاء المرتجع بنجاح! رقم المرتجع: {$returnInvoice->invoice_no}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * عرض مرتجع
     */
    public function show(Invoice $invoice)
    {
        if (!in_array($invoice->type, ['sale_return', 'purchase_return'])) {
            abort(404);
        }

        $invoice->load(['items.product', 'customer', 'supplier', 'payments']);
        return view('returns.show', compact('invoice'));
    }

    /**
     * قائمة المرتجعات
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'sale_return');

        $returns = Invoice::where('type', $type)
            ->with(['customer', 'supplier'])
            ->latest()
            ->paginate(15);

        return view('returns.index', compact('returns', 'type'));
    }
}