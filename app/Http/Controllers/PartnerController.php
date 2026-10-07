<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::withSum('paidCapitals', 'amount')
            ->withSum('withdrawals', 'amount')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('partners.index', compact('partners'));
    }

    public function create()
    {
        return view('partners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:partners,code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'profit_share' => ['required', 'numeric', 'min:0', 'max:100'],
            'capital_share' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Partner::create($validated);

        return redirect()->route('partners.index')->with('success', 'تم إضافة الشريك بنجاح.');
    }

    public function show(Partner $partner)
    {
        $partner->load(['capitals', 'withdrawals']);

        $totalCapital = (float) $partner->capitals()->where('status', 'paid')->sum('amount');
        $totalWithdrawals = (float) $partner->withdrawals()->sum('amount');

        return view('partners.show', compact('partner', 'totalCapital', 'totalWithdrawals'));
    }

    public function edit(Partner $partner)
    {
        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('partners', 'code')->ignore($partner->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'profit_share' => ['required', 'numeric', 'min:0', 'max:100'],
            'capital_share' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $partner->update($validated);

        return redirect()->route('partners.index')->with('success', 'تم تعديل الشريك بنجاح.');
    }

    public function destroy(Partner $partner)
    {
        if ($partner->capitals()->exists() || $partner->withdrawals()->exists()) {
            return redirect()->route('partners.index')->with('error', 'لا يمكن حذف الشريك لوجود عمليات مرتبطة به.');
        }

        $partner->delete();

        return redirect()->route('partners.index')->with('success', 'تم حذف الشريك بنجاح.');
    }
}