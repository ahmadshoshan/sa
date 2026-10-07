<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductCardController extends Controller
{
    public function show(Request $request, Product $product)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        // ========== 1. المعلومات الأساسية ==========
        $stocks = $product->stocks()->with('warehouse')->get();
        $totalStock = $stocks->sum('quantity');
        $stockStatus = $totalStock <= 0 ? 'out' :
            ((float)$product->min_stock > 0 && $totalStock <= (float)$product->min_stock ? 'low' : 'ok');

        // ========== 2. حركة المخزون ==========
        $movementsQuery = StockMovement::where('product_id', $product->id)
            ->with(['warehouse', 'user'])
            ->orderByDesc('id');

        if ($from) $movementsQuery->whereDate('created_at', '>=', $from);
        if ($to) $movementsQuery->whereDate('created_at', '<=', $to);

        $movements = $movementsQuery->get()->map(function ($m) {
            $m->in = in_array($m->movement_type, ['purchase', 'sale_return', 'opening']) ? (float)$m->quantity : 0;
            $m->out = in_array($m->movement_type, ['sale', 'purchase_return']) ? (float)$m->quantity : 0;
            return $m;
        });

        $running = 0;
        $movementsWithRunning = $movements->reverse()->map(function ($m) use (&$running) {
            $running = $running + $m->in - $m->out;
            // $running = $running - $m->in + $m->out;
            $m->running_before = $running + $m->in - $m->out;
            $m->running_after = $running;
            return $m;
        })->reverse()->values();

        // ========== 3. المشتريات ==========
        $purchasesQuery = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'purchase'))
            ->with(['invoice.supplier']);

        if ($from) $purchasesQuery->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '>=', $from));
        if ($to) $purchasesQuery->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '<=', $to));

        $purchases = $purchasesQuery->get();
        $purchaseQty = $purchases->sum('quantity');
        $purchaseTotal = $purchases->sum('total');
        $purchaseAvgCost = $purchaseQty > 0 ? $purchaseTotal / $purchaseQty : 0;

        // ========== 4. المبيعات ==========
        $salesQuery = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'sale'))
            ->with(['invoice.customer']);

        if ($from) $salesQuery->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '>=', $from));
        if ($to) $salesQuery->whereHas('invoice', fn($i) => $i->whereDate('invoice_date', '<=', $to));

        $sales = $salesQuery->get();
        $salesQty = $sales->sum('quantity');
        $salesTotal = $sales->sum('total');
        $salesAvgPrice = $salesQty > 0 ? $salesTotal / $salesQty : 0;

        // ========== 5. المرتجعات ==========
        $salesReturns = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'sale_return'))->get();
        $purchaseReturns = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'purchase_return'))->get();

        $salesReturnQty = $salesReturns->sum('quantity');
        $salesReturnTotal = $salesReturns->sum('total');
        $purchaseReturnQty = $purchaseReturns->sum('quantity');
        $purchaseReturnTotal = $purchaseReturns->sum('total');

        // ========== 6. حساب الأرباح ==========
        $costOfGoodsSold = $salesQty * (float)$product->cost_price;
        $grossProfit = $salesTotal - $costOfGoodsSold;
        $profitMargin = $salesTotal > 0 ? ($grossProfit / $salesTotal) * 100 : 0;

        // ========== 7. أفضل العملاء ==========
        $topCustomers = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'sale'))
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->selectRaw('customers.name as name, customers.code as code, SUM(invoice_items.quantity) as qty, SUM(invoice_items.total) as total')
            ->groupBy('customers.id', 'customers.name', 'customers.code')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ========== 8. أفضل الموردين ==========
        $topSuppliers = InvoiceItem::where('product_id', $product->id)
            ->whereHas('invoice', fn($i) => $i->where('type', 'purchase'))
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('suppliers', 'suppliers.id', '=', 'invoices.supplier_id')
            ->selectRaw('suppliers.name as name, suppliers.code as code, SUM(invoice_items.quantity) as qty, SUM(invoice_items.total) as total')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.code')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ========== 9. المبيعات الشهرية (آخر 12 شهر) ==========
        $monthlySales = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $label = now()->subMonths($i)->format('M Y');
            $total = InvoiceItem::where('product_id', $product->id)
                ->whereHas('invoice', function ($i) use ($month) {
                    $i->where('type', 'sale')
                      ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month]);
                })
                ->sum('total');
            $monthlySales[] = ['label' => $label, 'total' => (float)$total];
        }

        $money = fn($v) => number_format((float)$v, 2);

        return view('product_card.show', compact(
            'product', 'stocks', 'totalStock', 'stockStatus',
            'movementsWithRunning', 'purchases', 'purchaseQty', 'purchaseTotal', 'purchaseAvgCost',
            'sales', 'salesQty', 'salesTotal', 'salesAvgPrice',
            'salesReturnQty', 'salesReturnTotal', 'purchaseReturnQty', 'purchaseReturnTotal',
            'costOfGoodsSold', 'grossProfit', 'profitMargin',
            'topCustomers', 'topSuppliers', 'monthlySales',
            'from', 'to', 'money'
        ));
    }
}