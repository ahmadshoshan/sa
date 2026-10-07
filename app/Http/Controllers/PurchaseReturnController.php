<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('type', 'purchase_return')
            ->with('supplier')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('purchase_returns.index', compact('invoices'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $originalInvoices = Invoice::where('type', 'purchase')
            ->where('remaining_amount', '>', 0)
            ->with('supplier')
            ->orderByDesc('invoice_date')
            ->get();

        $products = Product::where('is_active', true)
            ->with('stocks')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'sale_price' => (float) $product->sale_price,
                    'cost_price' => (float) $product->cost_price,
                    'tax_rate' => (float) $product->tax_rate,
                    'stocks' => $product->stocks
                        ->mapWithKeys(function ($stock) {
                            return [$stock->warehouse_id => (float) $stock->quantity];
                        })
                        ->all(),
                ];
            });

        return view('purchase_returns.create', compact('suppliers', 'warehouses', 'originalInvoices', 'products'));
    }

    public function store(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'original_invoice_id' => ['nullable', 'exists:invoices,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $invoice = $invoiceService->createPurchaseReturn($validated, $validated['items']);

            return redirect()
                ->route('purchase-returns.show', $invoice)
                ->with('success', 'تم إنشاء مرتجع الشراء بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->type !== 'purchase_return') {
            abort(404);
        }

        $invoice->load(['supplier', 'warehouse', 'items.product', 'payments']);

        return view('purchase_returns.show', compact('invoice'));
    }
}