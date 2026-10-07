<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Payment;
use App\Models\Account;
use App\Models\JournalLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfService
{
    /**
     * طباعة فاتورة بيع/شراء
     */
    public function printInvoice(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'supplier', 'payments', 'warehouse']);
        
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'companyName', 'currency'))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);
        
        $filename = $invoice->invoice_no . '.pdf';
        return $pdf->stream($filename);
    }
    
    /**
     * طباعة كشف حساب عميل
     */
    public function printCustomerStatement(Customer $customer, ?string $from = null, ?string $to = null)
    {
        $query = Payment::where('customer_id', $customer->id);
        $invoiceQuery = Invoice::where('customer_id', $customer->id)->where('type', 'sale');
        
        if ($from) {
            $query->whereDate('payment_date', '>=', $from);
            $invoiceQuery->whereDate('invoice_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('payment_date', '<=', $to);
            $invoiceQuery->whereDate('invoice_date', '<=', $to);
        }
        
        $payments = $query->orderBy('payment_date')->get();
        $invoices = $invoiceQuery->orderBy('invoice_date')->get();
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.customer_statement', compact('customer', 'payments', 'invoices', 'from', 'to', 'companyName', 'currency'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->stream("statement_{$customer->code}.pdf");
    }
    
    /**
     * طباعة كشف حساب مورد
     */
    public function printSupplierStatement(Supplier $supplier, ?string $from = null, ?string $to = null)
    {
        $query = Payment::where('supplier_id', $supplier->id);
        $invoiceQuery = Invoice::where('supplier_id', $supplier->id)->where('type', 'purchase');
        
        if ($from) {
            $query->whereDate('payment_date', '>=', $from);
            $invoiceQuery->whereDate('invoice_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('payment_date', '<=', $to);
            $invoiceQuery->whereDate('invoice_date', '<=', $to);
        }
        
        $payments = $query->orderBy('payment_date')->get();
        $invoices = $invoiceQuery->orderBy('invoice_date')->get();
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.supplier_statement', compact('supplier', 'payments', 'invoices', 'from', 'to', 'companyName', 'currency'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->stream("statement_{$supplier->code}.pdf");
    }
    
    /**
     * طباعة تقرير المبيعات
     */
    public function printSalesReport(?string $from = null, ?string $to = null)
    {
        $query = Invoice::where('type', 'sale');
        
        if ($from) $query->whereDate('invoice_date', '>=', $from);
        if ($to) $query->whereDate('invoice_date', '<=', $to);
        
        $invoices = $query->with('customer')->orderBy('invoice_date', 'desc')->get();
        $total = $invoices->sum('total');
        $paid = $invoices->sum('paid_amount');
        $remaining = $invoices->sum('remaining_amount');
        
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.sales_report', compact('invoices', 'total', 'paid', 'remaining', 'from', 'to', 'companyName', 'currency'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->stream('sales_report.pdf');
    }
    
    /**
     * طباعة تقرير المشتريات
     */
    public function printPurchasesReport(?string $from = null, ?string $to = null)
    {
        $query = Invoice::where('type', 'purchase');
        
        if ($from) $query->whereDate('invoice_date', '>=', $from);
        if ($to) $query->whereDate('invoice_date', '<=', $to);
        
        $invoices = $query->with('supplier')->orderBy('invoice_date', 'desc')->get();
        $total = $invoices->sum('total');
        $paid = $invoices->sum('paid_amount');
        $remaining = $invoices->sum('remaining_amount');
        
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.purchases_report', compact('invoices', 'total', 'paid', 'remaining', 'from', 'to', 'companyName', 'currency'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->stream('purchases_report.pdf');
    }
    
    /**
     * طباعة سند قبض/صرف
     */
    public function printPayment(Payment $payment)
    {
        $payment->load(['customer', 'supplier', 'invoice', 'account']);
        
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.payment', compact('payment', 'companyName', 'currency'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->stream("payment_{$payment->payment_no}.pdf");
    }
    
    /**
     * طباعة قائمة الدخل
     */
    public function printIncomeStatement(?string $from = null, ?string $to = null)
    {
        $from = $from ?? now()->startOfMonth()->toDateString();
        $to = $to ?? today()->toDateString();
        
        $revenueIds = Account::where('type', 'revenue')->pluck('id');
        $expenseIds = Account::where('type', 'expense')->pluck('id');
        
        $revenue = (float) JournalLine::whereIn('account_id', $revenueIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as bal')
            ->value('bal') ?? 0;
        
        $expense = (float) JournalLine::whereIn('account_id', $expenseIds)
            ->whereHas('entry', fn($e) => $e->whereDate('entry_date', '>=', $from)->whereDate('entry_date', '<=', $to))
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as bal')
            ->value('bal') ?? 0;
        
        $net = $revenue - $expense;
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        
        $pdf = Pdf::loadView('pdf.income_statement', compact('revenue', 'expense', 'net', 'from', 'to', 'companyName', 'currency'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->stream('income_statement.pdf');
    }
    
    /**
     * طباعة ميزان المراجعة
     */
    public function printTrialBalance(?string $toDate = null)
    {
        $toDate = $toDate ?? today()->toDateString();
        $accounts = Account::where('is_active', true)->get();
        
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($accounts as $account) {
            $query = JournalLine::where('account_id', $account->id)
                ->whereHas('entry', function ($q) use ($toDate) {
                    $q->where('is_posted', true);
                    if ($toDate) $q->whereDate('entry_date', '<=', $toDate);
                });
            
            $debit = (float) $query->sum('debit');
            $credit = (float) $query->sum('credit');
            
            if ($debit == 0 && $credit == 0) continue;
            
            $rows[] = [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => $debit,
                'credit' => $credit,
            ];
            
            $totalDebit += $debit;
            $totalCredit += $credit;
        }
        
        $companyName = setting('company_name', 'الشركة');
        $currency = setting('currency', 'جنيه');
        $balanced = abs($totalDebit - $totalCredit) < 0.01;
        
        $pdf = Pdf::loadView('pdf.trial_balance', compact('rows', 'totalDebit', 'totalCredit', 'balanced', 'toDate', 'companyName', 'currency'))
            ->setPaper('a4', 'landscape');
        
        return $pdf->stream('trial_balance.pdf');
    }
}