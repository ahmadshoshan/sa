<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerWithdrawal;
use App\Services\PartnerWithdrawalService;
use Illuminate\Http\Request;
use Throwable;

class PartnerWithdrawalController extends Controller
{
    public function create(Partner $partner)
    {
        return view('partner_withdrawals.create', compact('partner'));
    }

    public function store(Request $request, Partner $partner, PartnerWithdrawalService $service)
    {
        // حماية من التكرار
        $idempotencyKey = $request->input('_idempotency_key');
        if ($idempotencyKey) {
            $existing = PartnerWithdrawal::findByIdempotencyKey($idempotencyKey);
            if ($existing) {
                return redirect()->route('partners.show', $partner)
                    ->with('warning', 'تم حفظ هذه العملية مسبقاً. لم يتم تكرارها.');
            }
        }

        // توليد مفتاح جديد إن لم يوجد
        if (! $idempotencyKey) {
            $idempotencyKey = PartnerWithdrawal::generateIdempotencyKey();
        }

        $validated = $request->validate([
            'withdrawal_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'notes' => ['nullable', 'string'],
        ]);
        $validated['idempotency_key'] = $idempotencyKey;

        try {
            $service->addWithdrawal($partner, $validated);

            return redirect()->route('partners.show', $partner)->with('success', 'تم تسجيل المسحوبات بنجاح.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
