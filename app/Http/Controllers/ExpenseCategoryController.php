<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with('account')->orderBy('id', 'desc')->paginate(15);

        return view('expense_categories.index', compact('categories'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'expense')->orderBy('code')->get();

        return view('expense_categories.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:expense_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'account_id' => ['nullable', 'exists:accounts,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')->with('success', 'تم إضافة تصنيف المصروفات بنجاح.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $accounts = Account::where('type', 'expense')->orderBy('code')->get();

        return view('expense_categories.edit', compact('expenseCategory', 'accounts'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('expense_categories', 'code')->ignore($expenseCategory->id)],
            'name' => ['required', 'string', 'max:255'],
            'account_id' => ['nullable', 'exists:accounts,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')->with('success', 'تم تعديل تصنيف المصروفات بنجاح.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return redirect()->route('expense-categories.index')->with('error', 'لا يمكن حذف التصنيف لوجود مصروفات مرتبطة به.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')->with('success', 'تم حذف تصنيف المصروفات بنجاح.');
    }
}