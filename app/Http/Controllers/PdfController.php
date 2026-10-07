<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Payment;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    protected PdfService $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function invoice(Invoice $invoice)
    {
        return $this->pdfService->printInvoice($invoice);
    }

    public function customerStatement(Request $request, Customer $customer)
    {
        return $this->pdfService->printCustomerStatement(
            $customer,
            $request->input('from'),
            $request->input('to')
        );
    }

    public function supplierStatement(Request $request, Supplier $supplier)
    {
        return $this->pdfService->printSupplierStatement(
            $supplier,
            $request->input('from'),
            $request->input('to')
        );
    }

    public function salesReport(Request $request)
    {
        return $this->pdfService->printSalesReport(
            $request->input('from'),
            $request->input('to')
        );
    }

    public function purchasesReport(Request $request)
    {
        return $this->pdfService->printPurchasesReport(
            $request->input('from'),
            $request->input('to')
        );
    }

    public function payment(Payment $payment)
    {
        return $this->pdfService->printPayment($payment);
    }

    public function incomeStatement(Request $request)
    {
        return $this->pdfService->printIncomeStatement(
            $request->input('from'),
            $request->input('to')
        );
    }

    public function trialBalance(Request $request)
    {
        return $this->pdfService->printTrialBalance($request->input('to'));
    }
}