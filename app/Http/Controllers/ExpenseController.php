<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Partner;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Throwable;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['category', 'partner', 'user'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $partners = Partner::where('is_active', true)->orderBy('name')->get();
        $idempotencyKey = Expense::generateIdempotencyKey();

        return view('expenses.create', compact('categories', 'partners', 'idempotencyKey'));
    }

    public function store(Request $request, ExpenseService $expenseService)
    {
        $validated = $request->validate([
            '_idempotency_key' => ['nullable', 'string', 'max:255'],
            'expense_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque,credit'],
            'partner_id' => ['nullable', 'exists:partners,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $idempotencyKey = $validated['_idempotency_key'] ?? Expense::generateIdempotencyKey();
        $existing = Expense::findByIdempotencyKey($idempotencyKey);
        if ($existing) {
            return redirect()->route('expenses.show', $existing)
                ->with('warning', 'تم حفظ هذه العملية مسبقاً. لم يتم تكرارها.');
        }

        $validated['idempotency_key'] = $idempotencyKey;

        try {
            $expense = $expenseService->createExpense($validated);

            return redirect()->route('expenses.show', $expense)->with('success', 'تم تسجيل المصروف بنجاح.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Expense $expense)
    {
        $expense->load(['category.account', 'partner', 'payments']);

        return view('expenses.show', compact('expense'));
    }
}