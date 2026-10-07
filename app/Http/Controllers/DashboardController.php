<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();
        $monthStart = now()->startOfMonth();

        // KPIs
        $todaySales = (float) Invoice::where('type', 'sale')->whereDate('invoice_date', $today)->sum('total');
        $monthSales = (float) Invoice::where('type', 'sale')->whereDate('invoice_date', '>=', $monthStart)->sum('total');
        $monthPurchases = (float) Invoice::where('type', 'purchase')->whereDate('invoice_date', '>=', $monthStart)->sum('total');
        $receivables = (float) Customer::sum('current_balance');
        $payables = (float) Supplier::sum('current_balance');
        $customersCount = Customer::count();
        $productsCount = Product::count();

        $lowStock = Product::withSum('stocks', 'quantity')
            ->get()
            ->filter(fn($p) => ((float) ($p->stocks_sum_quantity ?? 0)) <= (float) $p->min_stock)
            ->count();

        // Daily sales last 30 days
        $dailyMap = [];

        for ($i = 29; $i >= 0; $i--) {
            $dailyMap[now()->subDays($i)->format('Y-m-d')] = 0;
        }

        $daily = Invoice::where('type', 'sale')
            ->whereDate('invoice_date', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(invoice_date) as d, SUM(total) as total')
            ->groupBy('d')
            ->get();

        foreach ($daily as $row) {
            $dailyMap[$row->d] = (float) $row->total;
        }

        $dailyLabels = array_map(fn($d) => substr($d, 8), array_keys($dailyMap));
        $dailyData = array_values($dailyMap);

        // Monthly sales last 12 months
        $monthlyMap = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthlyMap[now()->subMonths($i)->format('Y-m')] = 0;
        }

        $monthly = Invoice::where('type', 'sale')
            ->whereDate('invoice_date', '>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as m, SUM(total) as total")
            ->groupBy('m')
            ->get();

        foreach ($monthly as $row) {
            $monthlyMap[$row->m] = (float) $row->total;
        }

        $monthlyLabels = array_keys($monthlyMap);
        $monthlyData = array_values($monthlyMap);

        // Sales by category
        $categorySales = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('invoices.type', 'sale')
            ->whereDate('invoices.invoice_date', '>=', now()->subDays(29))
            ->selectRaw('COALESCE(categories.name, "غير مصنف") as name, SUM(invoice_items.total) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Top products
        $topProducts = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.type', 'sale')
            ->whereDate('invoices.invoice_date', '>=', now()->subDays(29))
            ->selectRaw('products.name as name, SUM(invoice_items.total) as total')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Payment methods
        $paymentMethods = Payment::selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return view('dashboard.index', compact(
            'todaySales', 'monthSales', 'monthPurchases', 'receivables', 'payables',
            'customersCount', 'productsCount', 'lowStock',
            'dailyLabels', 'dailyData', 'monthlyLabels', 'monthlyData',
            'categorySales', 'topProducts', 'paymentMethods'
        ));
    }
}