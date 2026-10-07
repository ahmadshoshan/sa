<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;

class AdvancedReportController extends Controller
{
    public function salesByProduct(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', today()->toDateString());

        $rows = InvoiceItem::join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.type', 'sale')
            ->whereDate('invoices.invoice_date', '>=', $from)
            ->whereDate('invoices.invoice_date', '<=', $to)
            ->selectRaw('products.name as name, SUM(invoice_items.quantity) as qty, SUM(invoice_items.total) as total, SUM(invoice_items.total - (invoice_items.cost_price * invoice_items.quantity)) as profit')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->get();

        return view('advanced_reports.sales_by_product', compact('rows', 'from', 'to'));
    }

    public function slowMoving()
    {
        $rows = Product::withSum('stocks', 'quantity')
            ->where('is_active', true)
            ->whereDoesntHave('invoiceItems', function ($q) {
                $q->whereHas('invoice', fn($i) => $i->where('type', 'sale'))
                    ->whereDate('created_at', '>=', now()->subDays(30));
            })
            ->orderBy('name')
            ->get();

        return view('advanced_reports.slow_moving', compact('rows'));
    }

    public function receivablesAgeing()
    {
        $customers = Customer::where('current_balance', '>', 0)->get();

        return view('advanced_reports.receivables_ageing', compact('customers'));
    }

    public function topCustomers(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', today()->toDateString());

        $rows = \App\Models\Invoice::where('type', 'sale')
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->with('customer')
            ->selectRaw('customer_id, SUM(total) as total, COUNT(*) as count')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        return view('advanced_reports.top_customers', compact('rows', 'from', 'to'));
    }
}