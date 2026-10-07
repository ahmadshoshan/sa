<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use App\Services\PayablesService;
use Illuminate\Http\Request;

class PayablesController extends Controller
{
    protected PayablesService $payablesService;

    public function __construct(PayablesService $payablesService)
    {
        $this->payablesService = $payablesService;
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'expenses');

        $payableExpenses = $this->payablesService->getPayableExpenses();
        $payableInvoices = $this->payablesService->getPayableInvoices();
        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();

        $totalExpenses = $payableExpenses->sum('remaining_amount');
        $totalInvoices = $payableInvoices->sum('remaining_amount');

        return view('payables.index', compact(
            'tab', 'payableExpenses', 'payableInvoices',
            'cashAccounts', 'totalExpenses', 'totalInvoices'
        ));
    }

    // دفع مصروف
    public function payExpenseForm(Expense $expense)
    {
        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();
        return view('payables.pay_expense', compact('expense', 'cashAccounts'));
    }

    public function payExpense(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $expense->remaining_amount],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->payablesService->payExpense(
                $expense->id,
                (float) $validated['amount'],
                $validated['payment_method'],
                (int) $validated['account_id'],
                $validated['notes'] ?? null
            );

            return redirect()->route('payables.index')->with('success', 'تم دفع المصروف بنجاح! رقم السند: ' . $payment->payment_no);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // دفع فاتورة مشتريات
    public function payInvoiceForm(Invoice $invoice)
    {
        if ($invoice->type !== 'purchase') {
            return redirect()->route('payables.index')->with('error', 'هذه ليست فاتورة شراء');
        }

        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();
        return view('payables.pay_invoice', compact('invoice', 'cashAccounts'));
    }

    public function payInvoice(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->remaining_amount],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->payablesService->payInvoice(
                $invoice->id,
                (float) $validated['amount'],
                $validated['payment_method'],
                (int) $validated['account_id'],
                $validated['notes'] ?? null
            );

            return redirect()->route('payables.index', ['tab' => 'invoices'])->with('success', 'تم دفع الفاتورة بنجاح! رقم السند: ' . $payment->payment_no);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // دفع مرتب
    public function paySalaryForm()
    {
        $cashAccounts = Account::whereIn('code', ['1001', '1002', '1003'])->where('is_active', true)->get();
        return view('payables.pay_salary', compact('cashAccounts'));
    }

    public function paySalary(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'account_id' => ['required', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->payablesService->paySalary(
                $validated['employee_name'],
                (float) $validated['amount'],
                $validated['payment_method'],
                (int) $validated['account_id'],
                $validated['notes'] ?? null
            );

            return redirect()->route('payables.index', ['tab' => 'salaries'])->with('success', 'تم دفع المرتب بنجاح! رقم السند: ' . $payment->payment_no);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}