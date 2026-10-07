<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerDistribution;
use App\Models\PartnerDistributionItem;
use Illuminate\Http\Request;

class CompanyReportController extends Controller
{
    public function partnerStatement(Partner $partner)
    {
        $partner->load(['capitals', 'withdrawals']);

        $totalCapital = (float) $partner->capitals()
            ->where('status', 'paid')
            ->sum('amount');

        $totalWithdrawals = (float) $partner->withdrawals()->sum('amount');

        $allocatedProfits = (float) PartnerDistributionItem::where('partner_id', $partner->id)
            ->whereHas('distribution', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('final_amount');

        $paidProfits = (float) PartnerDistributionItem::where('partner_id', $partner->id)
            ->whereHas('distribution', function ($query) {
                $query->where('status', 'posted');
            })
            ->sum('paid_amount');

        $remainingProfits = $allocatedProfits - $paidProfits;

        $netEquity = $totalCapital + $allocatedProfits - $totalWithdrawals - $paidProfits;

        $distributionItems = PartnerDistributionItem::where('partner_id', $partner->id)
            ->whereHas('distribution', function ($query) {
                $query->where('status', 'posted');
            })
            ->with('distribution')
            ->orderByDesc('id')
            ->get();

        return view('company_reports.partner_statement', compact(
            'partner',
            'totalCapital',
            'totalWithdrawals',
            'allocatedProfits',
            'paidProfits',
            'remainingProfits',
            'netEquity',
            'distributionItems'
        ));
    }

    public function partnersSummary()
    {
        $partners = Partner::withSum('paidCapitals as capital_sum_amount', 'amount')
            ->withSum('withdrawals as withdrawals_sum_amount', 'amount')
            ->orderBy('id')
            ->get();

        foreach ($partners as $partner) {
            $allocated = (float) PartnerDistributionItem::where('partner_id', $partner->id)
                ->whereHas('distribution', function ($query) {
                    $query->where('status', 'posted');
                })
                ->sum('final_amount');

            $paid = (float) PartnerDistributionItem::where('partner_id', $partner->id)
                ->whereHas('distribution', function ($query) {
                    $query->where('status', 'posted');
                })
                ->sum('paid_amount');

            $partner->allocated_profit = $allocated;
            $partner->paid_profit = $paid;
            $partner->remaining_profit = $allocated - $paid;

            $partner->net_equity = (float) ($partner->capital_sum_amount ?? 0)
                + $allocated
                - (float) ($partner->withdrawals_sum_amount ?? 0)
                - $paid;
        }

        return view('company_reports.partners_summary', compact('partners'));
    }

    public function distributionsSummary()
    {
        $distributions = PartnerDistribution::withSum('items as final_sum', 'final_amount')
            ->withSum('items as paid_sum', 'paid_amount')
            ->orderByDesc('id')
            ->paginate(15);

        return view('company_reports.distributions_summary', compact('distributions'));
    }

    public function distributionItems(Request $request)
    {
        $partners = Partner::orderBy('name')->get();

        $items = PartnerDistributionItem::with(['partner', 'distribution'])
            ->whereHas('distribution', function ($query) use ($request) {
                if ($request->filled('from')) {
                    $query->whereDate('period_from', '>=', $request->from);
                }

                if ($request->filled('to')) {
                    $query->whereDate('period_to', '<=', $request->to);
                }
            })
            ->when($request->filled('partner_id'), function ($query) use ($request) {
                $query->where('partner_id', $request->partner_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('company_reports.distribution_items', compact('items', 'partners'));
    }
}