<?php

namespace App\Http\Controllers;

use App\Models\PartnerDistribution;
use App\Models\PartnerDistributionItem;
use App\Services\ProfitDistributionService;
use Illuminate\Http\Request;
use Throwable;

class ProfitDistributionController extends Controller
{
    public function index()
    {
        $distributions = PartnerDistribution::withCount('items')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('profit_distributions.index', compact('distributions'));
    }

    public function create()
    {
        return view('profit_distributions.create');
    }

    public function store(Request $request, ProfitDistributionService $service)
    {
        $validated = $request->validate([
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'reserve_amount' => ['nullable', 'numeric', 'min:0'],
            'distribute_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $distribution = $service->createDraft($validated);

            return redirect()
                ->route('distributions.show', $distribution)
                ->with('success', 'تم إنشاء مسودة توزيع الأرباح بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(PartnerDistribution $distribution)
    {
        $distribution->load(['items.partner']);

        return view('profit_distributions.show', compact('distribution'));
    }

    public function approve(PartnerDistribution $distribution, ProfitDistributionService $service)
    {
        try {
            $service->approve($distribution);

            return redirect()
                ->route('distributions.show', $distribution)
                ->with('success', 'تم اعتماد توزيع الأرباح بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function payItem(Request $request, PartnerDistributionItem $item, ProfitDistributionService $service)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,cheque'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $service->payItem(
                $item,
                (float) $validated['amount'],
                $validated['payment_method'],
                $validated['payment_date'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->route('distributions.show', $item->distribution_id)
                ->with('success', 'تم سداد نصيب الشريك بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}