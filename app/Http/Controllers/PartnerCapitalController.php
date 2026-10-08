<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerCapital;
use App\Services\PartnerCapitalService;
use Illuminate\Http\Request;
use Throwable;

class PartnerCapitalController extends Controller
{
    public function create(Partner $partner)
    {
        return view('partner_capitals.create', compact('partner'));
    }

    public function store(Request $request, Partner $partner, PartnerCapitalService $service)
    {
        // حماية من التكرار
        $idempotencyKey = $request->input('_idempotency_key');
        if ($idempotencyKey) {
            $existing = PartnerCapital::findByIdempotencyKey($idempotencyKey);
            if ($existing) {
                return redirect()->back()
                    ->with('warning', 'تم حفظ هذه العملية مسبقاً. لم يتم تكرارها.');
            }
        }

        if (! $idempotencyKey) {
            $idempotencyKey = PartnerCapital::generateIdempotencyKey();
        }

        $validated = $request->validate([
            'contribution_date' => ['required', 'date'],
            'contribution_type' => ['required', 'in:cash,inventory,asset,other'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', 'in:cash,card,bank_transfer,cheque,credit'],
            'description' => ['nullable', 'string'],
        ]);
        $validated['idempotency_key'] = $idempotencyKey;

        try {
            $service->addCapital($partner, $validated);

            return redirect()->route('partners.show', $partner)->with('success', 'تم تسجيل رأس المال بنجاح.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
