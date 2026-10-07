<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('id', 'desc')->paginate(10);

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Warehouse::create($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم إضافة المخزن بنجاح.');
    }

    public function show(Warehouse $warehouse)
    {
        return redirect()->route('warehouses.index');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($warehouse->id),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $warehouse->update($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم تعديل المخزن بنجاح.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if (
            $warehouse->stocks()->exists() ||
            $warehouse->stockMovements()->exists() ||
            $warehouse->invoices()->exists()
        ) {
            return redirect()
                ->route('warehouses.index')
                ->with('error', 'لا يمكن حذف المخزن لوجود بيانات مرتبطة به.');
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم حذف المخزن بنجاح.');
    }
}