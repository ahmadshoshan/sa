<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('id', 'desc')->paginate(10);

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:units,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Unit::create($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'تم إضافة الوحدة بنجاح.');
    }

    public function show(Unit $unit)
    {
        return redirect()->route('units.index');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('units', 'code')->ignore($unit->id),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $unit->update($validated);

        return redirect()
            ->route('units.index')
            ->with('success', 'تم تعديل الوحدة بنجاح.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return redirect()
                ->route('units.index')
                ->with('error', 'لا يمكن حذف الوحدة لأن لها أصناف مرتبطة بها.');
        }

        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'تم حذف الوحدة بنجاح.');
    }
}