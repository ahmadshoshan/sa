<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $todaySales = Invoice::where('type', 'sale')
            ->whereDate('invoice_date', today())
            ->sum('total');

        $monthSales = Invoice::where('type', 'sale')
            ->whereBetween('invoice_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $salesCount = Invoice::where('type', 'sale')->count();
        $customersCount = Customer::count();
        $suppliersCount = Supplier::count();
        $productsCount = Product::count();

        $lowStockProducts = Product::withSum('stocks', 'quantity')
            ->get()
            ->filter(function ($product) {
                return ((float) ($product->stocks_sum_quantity ?? 0)) <= (float) $product->min_stock;
            })
            ->take(10)
            ->values();

        $latestSales = Invoice::where('type', 'sale')
            ->with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('reports.index', compact(
            'todaySales',
            'monthSales',
            'salesCount',
            'customersCount',
            'suppliersCount',
            'productsCount',
            'lowStockProducts',
            'latestSales'
        ));
    }

    public function sales(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        $base = Invoice::where('type', 'sale');

        if ($request->filled('from')) {
            $base->whereDate('invoice_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $base->whereDate('invoice_date', '<=', $request->to);
        }

        if ($request->filled('customer_id')) {
            $base->where('customer_id', $request->customer_id);
        }

        $summary = (clone $base)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sum')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid_sum')
            ->selectRaw('COALESCE(SUM(remaining_amount), 0) as remaining_sum')
            ->selectRaw('COUNT(*) as invoices_count')
            ->first();

        $invoices = $base->with('customer')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.sales', compact('invoices', 'summary', 'customers'));
    }

    public function purchases(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $base = Invoice::where('type', 'purchase');

        if ($request->filled('from')) {
            $base->whereDate('invoice_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $base->whereDate('invoice_date', '<=', $request->to);
        }

        if ($request->filled('supplier_id')) {
            $base->where('supplier_id', $request->supplier_id);
        }

        $summary = (clone $base)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sum')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid_sum')
            ->selectRaw('COALESCE(SUM(remaining_amount), 0) as remaining_sum')
            ->selectRaw('COUNT(*) as invoices_count')
            ->first();

        $invoices = $base->with('supplier')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.purchases', compact('invoices', 'summary', 'suppliers'));
    }

    public function customers(Request $request)
    {
        $customers = Customer::query();

        if ($request->filled('q')) {
            $q = $request->q;

            $customers->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $customers = $customers->orderBy('current_balance', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.customers', compact('customers'));
    }

    public function suppliers(Request $request)
    {
        $suppliers = Supplier::query();

        if ($request->filled('q')) {
            $q = $request->q;

            $suppliers->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $suppliers = $suppliers->orderBy('current_balance', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.suppliers', compact('suppliers'));
    }

    public function stock(Request $request)
    {
        $products = Product::with(['category', 'unit'])
            ->withSum('stocks', 'quantity');

        if ($request->filled('q')) {
            $q = $request->q;

            $products->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        $products = $products->orderBy('products.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock', compact('products'));
    }

    public function stockMovements(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        $movements = StockMovement::with(['product', 'warehouse']);

        if ($request->filled('product_id')) {
            $movements->where('product_id', $request->product_id);
        }

        if ($request->filled('warehouse_id')) {
            $movements->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('movement_type')) {
            $movements->where('movement_type', $request->movement_type);
        }

        if ($request->filled('from')) {
            $movements->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $movements->whereDate('created_at', '<=', $request->to);
        }

        $movements = $movements->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.stock_movements', compact('movements', 'products', 'warehouses'));
    }

    public function payments(Request $request)
    {
        $payments = Payment::with(['customer', 'supplier', 'invoice']);

        if ($request->filled('type')) {
            $payments->where('type', $request->type);
        }

        if ($request->filled('payment_method')) {
            $payments->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from')) {
            $payments->whereDate('payment_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $payments->whereDate('payment_date', '<=', $request->to);
        }

        $payments = $payments->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('reports.payments', compact('payments'));
    }

    public function profit(Request $request)
    {
        $salesRevenue = $this->applyInvoiceDateFilter(Invoice::where('type', 'sale'), $request)->sum('total');
        $salesReturnRevenue = $this->applyInvoiceDateFilter(Invoice::where('type', 'sale_return'), $request)->sum('total');

        $salesItemCost = InvoiceItem::whereHas('invoice', function ($query) use ($request) {
                $query->where('type', 'sale');
                $this->applyInvoiceDateFilter($query, $request);
            })
            ->selectRaw('COALESCE(SUM(cost_price * quantity), 0) as cost')
            ->value('cost');

        $salesReturnItemCost = InvoiceItem::whereHas('invoice', function ($query) use ($request) {
                $query->where('type', 'sale_return');
                $this->applyInvoiceDateFilter($query, $request);
            })
            ->selectRaw('COALESCE(SUM(cost_price * quantity), 0) as cost')
            ->value('cost');

        $salesItemProfit = InvoiceItem::whereHas('invoice', function ($query) use ($request) {
                $query->where('type', 'sale');
                $this->applyInvoiceDateFilter($query, $request);
            })
            ->selectRaw('COALESCE(SUM(total - (cost_price * quantity)), 0) as profit')
            ->value('profit');

        $salesReturnItemProfit = InvoiceItem::whereHas('invoice', function ($query) use ($request) {
                $query->where('type', 'sale_return');
                $this->applyInvoiceDateFilter($query, $request);
            })
            ->selectRaw('COALESCE(SUM(total - (cost_price * quantity)), 0) as profit')
            ->value('profit');

        $netRevenue = (float) $salesRevenue - (float) $salesReturnRevenue;
        $netCost = (float) $salesItemCost - (float) $salesReturnItemCost;
        $netProfit = (float) $salesItemProfit - (float) $salesReturnItemProfit;

        return view('reports.profit', compact(
            'salesRevenue',
            'salesReturnRevenue',
            'netRevenue',
            'salesItemCost',
            'salesReturnItemCost',
            'netCost',
            'salesItemProfit',
            'salesReturnItemProfit',
            'netProfit'
        ));
    }

    private function applyInvoiceDateFilter($query, Request $request)
    {
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }

        return $query;
    }
}