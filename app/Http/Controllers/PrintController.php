<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;

class PrintController extends Controller
{
    private function settings()
    {
        return Setting::pluck('value', 'key');
    }

    public function saleA4(Invoice $invoice)
    {
        if ($invoice->type !== 'sale') {
            abort(404);
        }

        $invoice->load(['customer', 'warehouse', 'items.product', 'payments']);
        $settings = $this->settings();

        return view('sales.print_a4', compact('invoice', 'settings'));
    }

    public function saleThermal(Invoice $invoice)
    {
        if ($invoice->type !== 'sale') {
            abort(404);
        }

        $invoice->load(['customer', 'items.product']);
        $settings = $this->settings();

        return view('sales.print_thermal', compact('invoice', 'settings'));
    }

    public function purchaseA4(Invoice $invoice)
    {
        if ($invoice->type !== 'purchase') {
            abort(404);
        }

        $invoice->load(['supplier', 'warehouse', 'items.product', 'payments']);
        $settings = $this->settings();

        return view('purchases.print_a4', compact('invoice', 'settings'));
    }

    public function saleReturnA4(Invoice $invoice)
    {
        if ($invoice->type !== 'sale_return') {
            abort(404);
        }

        $invoice->load(['customer', 'warehouse', 'items.product', 'payments']);
        $settings = $this->settings();

        return view('sales_returns.print_a4', compact('invoice', 'settings'));
    }

    public function purchaseReturnA4(Invoice $invoice)
    {
        if ($invoice->type !== 'purchase_return') {
            abort(404);
        }

        $invoice->load(['supplier', 'warehouse', 'items.product', 'payments']);
        $settings = $this->settings();

        return view('purchase_returns.print_a4', compact('invoice', 'settings'));
    }

    public function paymentVoucher(Payment $payment)
    {
        $payment->load(['customer', 'supplier', 'invoice']);
        $settings = $this->settings();

        return view('payments.print', compact('payment', 'settings'));
    }
}