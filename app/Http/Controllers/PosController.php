<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'phone']);

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::where('is_active', true)
            ->with('stocks')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'sale_price' => (float) $product->sale_price,
                    'tax_rate' => (float) $product->tax_rate,
                    'category_id' => $product->category_id,
                    'image' => $product->image,
                    'image_url' => $product->image ? asset($product->image) : null,
                    'stocks' => $product->stocks
                        ->mapWithKeys(function ($stock) {
                            return [$stock->warehouse_id => (float) $stock->quantity];
                        })
                        ->all(),
                ];
            });

        return view('pos.index', compact('customers', 'warehouses', 'categories', 'products'));
    }

    public function storeQuickCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $code = 'C' . str_pad(((int) Customer::max('id')) + 1, 5, '0', STR_PAD_LEFT);

            $customer = Customer::create([
                'code' => $code,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'current_balance' => $validated['opening_balance'] ?? 0,
                'price_level' => 'retail',
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'code' => $customer->code,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkout(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque,credit'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['invoice_date'] = now()->format('Y-m-d');

        try {
            $invoice = $invoiceService->createSale($validated, $validated['items']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_no' => $invoice->invoice_no,
                    'invoice_id' => $invoice->id,
                    'total' => (float) $invoice->total,
                    'print_a4' => route('sales.print.a4', $invoice),
                    'print_thermal' => route('sales.print.thermal', $invoice),
                ]);
            }

            return redirect()
                ->route('pos.index')
                ->with('success', "تم إنشاء الفاتورة {$invoice->invoice_no} بنجاح.");
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->route('pos.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * بحث سريع عن المنتجات للـ POS
     */
    public function quickSearch(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['products' => []]);
        }
        
        $products = \App\Models\Product::withSum('stocks', 'quantity')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('barcode', $query);
            })
            ->where('is_active', true)
            ->limit(20)
            ->get()
            ->map(function ($p) {
                $stock = (float) ($p->stocks_sum_quantity ?? 0);
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'barcode' => $p->barcode,
                    'price' => (float) $p->sale_price,
                    'wholesale_price' => (float) $p->wholesale_price,
                    'cost_price' => (float) $p->cost_price,
                    'stock' => $stock,
                    'in_stock' => $stock > 0,
                    'image' => $p->image ? asset('storage/' . $p->image) : null,
                    'unit' => $p->unit?->name ?? 'قطعة',
                ];
            });
        
        return response()->json(['products' => $products]);
    }

    /**
     * مسح باركود
     */
    public function scanBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        
        if (empty($barcode)) {
            return response()->json(['error' => 'الباركود مطلوب'], 400);
        }
        
        $product = \App\Models\Product::where('barcode', $barcode)
            ->orWhere('code', $barcode)
            ->where('is_active', true)
            ->first();
        
        if (!$product) {
            return response()->json(['error' => 'المنتج غير موجود'], 404);
        }
        
        $stock = (float) $product->stocks()->sum('quantity');
        
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'price' => (float) $product->sale_price,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'unit' => $product->unit?->name ?? 'قطعة',
        ]);
    }
}