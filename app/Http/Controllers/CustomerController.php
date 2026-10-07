<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('id', 'desc')->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:customers,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'price_level' => ['required', 'in:retail,wholesale,special'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['opening_balance'] = (float) $request->input('opening_balance', 0);
        $validated['current_balance'] = $validated['opening_balance'];
        $validated['credit_limit'] = (float) $request->input('credit_limit', 0);
        $validated['is_active'] = $request->boolean('is_active');

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'تم إضافة العميل بنجاح.');
    }

    public function show(Customer $customer)
    {
        return redirect()->route('customers.index');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('customers', 'code')->ignore($customer->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'price_level' => ['required', 'in:retail,wholesale,special'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['credit_limit'] = (float) $request->input('credit_limit', 0);

        $newOpening = (float) $request->input('opening_balance', 0);
        $oldOpening = (float) $customer->opening_balance;

        $validated['opening_balance'] = $newOpening;
        $validated['current_balance'] = (float) $customer->current_balance + ($newOpening - $oldOpening);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'تم تعديل العميل بنجاح.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->invoices()->exists() || $customer->payments()->exists()) {
            return redirect()
                ->route('customers.index')
                ->with('error', 'لا يمكن حذف العميل لوجود عمليات مرتبطة به.');
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح.');
    }
}