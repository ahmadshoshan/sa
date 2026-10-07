<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class SaleInvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::where('type', 'sale')
            ->with('customer')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('sales.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
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
                    'tax_rate' => (float) $product->tax_rate,
                    'stocks' => $product->stocks
                        ->mapWithKeys(function ($stock) {
                            return [$stock->warehouse_id => (float) $stock->quantity];
                        })
                        ->all(),
                ];
            });

        return view('sales.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $invoice = $invoiceService->createSale($validated, $validated['items']);

            return redirect()
                ->route('sales.show', $invoice)
                ->with('success', 'تم إنشاء فاتورة البيع بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->type !== 'sale') {
            abort(404);
        }

        $invoice->load(['customer', 'warehouse', 'items.product', 'payments']);

        return view('sales.show', compact('invoice'));
    }
}