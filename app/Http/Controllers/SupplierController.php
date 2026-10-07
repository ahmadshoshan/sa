<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['opening_balance'] = (float) $request->input('opening_balance', 0);
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['is_active'] = $request->boolean('is_active');

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'تم إضافة المورد بنجاح.');
    }

    public function show(Supplier $supplier)
    {
        return redirect()->route('suppliers.index');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($supplier->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $newOpening = (float) $request->input('opening_balance', 0);
        $oldOpening = (float) $supplier->opening_balance;

        $validated['opening_balance'] = $newOpening;
        $validated['current_balance'] = (float) $supplier->current_balance + ($newOpening - $oldOpening);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'تم تعديل المورد بنجاح.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->invoices()->exists() || $supplier->payments()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', 'لا يمكن حذف المورد لوجود عمليات مرتبطة به.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح.');
    }
}