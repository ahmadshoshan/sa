<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function trialBalance(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $applyPeriod = function ($query) use ($from, $to) {
            $query->where('is_posted', true);
            if ($from) $query->whereDate('entry_date', '>=', $from);
            if ($to) $query->whereDate('entry_date', '<=', $to);
        };

        $periodSums = JournalLine::selectRaw('account_id, COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
            ->whereHas('entry', $applyPeriod)
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $openingSums = collect();

        if ($from) {
            $applyOpening = function ($query) use ($from) {
                $query->where('is_posted', true)->whereDate('entry_date', '<', $from);
            };

            $openingSums = JournalLine::selectRaw('account_id, COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
                ->whereHas('entry', $applyOpening)
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');
        }

        $accountIds = $periodSums->keys()->merge($openingSums->keys())->unique()->values()->all();
        $accounts = Account::whereIn('id', $accountIds)->orderBy('code')->get();

        $rows = collect();
        $totals = ['opening_debit' => 0, 'opening_credit' => 0, 'debit' => 0, 'credit' => 0, 'closing_debit' => 0, 'closing_credit' => 0];

        foreach ($accounts as $account) {
            $openingDebit = (float) ($openingSums[$account->id]->debit ?? 0);
            $openingCredit = (float) ($openingSums[$account->id]->credit ?? 0);
            $opening = $openingDebit - $openingCredit;

            $debit = (float) ($periodSums[$account->id]->debit ?? 0);
            $credit = (float) ($periodSums[$account->id]->credit ?? 0);
            $closing = $opening + $debit - $credit;

            if (abs($opening) < 0.005 && abs($debit) < 0.005 && abs($credit) < 0.005) continue;

            $rows->push([
                'account' => $account, 'opening' => $opening, 'debit' => $debit, 'credit' => $credit, 'closing' => $closing,
            ]);

            if ($opening >= 0) $totals['opening_debit'] += $opening; else $totals['opening_credit'] += abs($opening);
            $totals['debit'] += $debit;
            $totals['credit'] += $credit;
            if ($closing >= 0) $totals['closing_debit'] += $closing; else $totals['closing_credit'] += abs($closing);
        }

        return view('financial.trial_balance', compact('rows', 'totals', 'from', 'to'));
    }

    public function ledger(Request $request)
    {
        $accounts = Account::orderBy('code')->get();
        $from = $request->input('from');
        $to = $request->input('to');
        $accountId = $request->integer('account_id');

        $account = null; $lines = collect(); $opening = 0; $totalDebit = 0; $totalCredit = 0; $closing = 0;

        if ($accountId) {
            $account = Account::find($accountId);
            if (!$account) return redirect()->back()->with('error', 'الحساب غير موجود.');

            if ($from) {
                $applyOpening = function ($query) use ($from) {
                    $query->where('is_posted', true)->whereDate('entry_date', '<', $from);
                };
                $openingSum = JournalLine::selectRaw('COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
                    ->where('account_id', $account->id)->whereHas('entry', $applyOpening)->first();
                $opening = (float) $openingSum->debit - (float) $openingSum->credit;
            }

            $applyPeriod = function ($query) use ($from, $to) {
                $query->where('is_posted', true);
                if ($from) $query->whereDate('entry_date', '>=', $from);
                if ($to) $query->whereDate('entry_date', '<=', $to);
            };

            $lines = JournalLine::where('account_id', $account->id)->whereHas('entry', $applyPeriod)->with('entry')
                ->get()->sortBy(fn($line) => [$line->entry?->entry_date, $line->journal_entry_id])->values();

            $totalDebit = (float) $lines->sum('debit');
            $totalCredit = (float) $lines->sum('credit');
            $closing = $opening + $totalDebit - $totalCredit;
        }

        return view('financial.ledger', compact('accounts', 'account', 'lines', 'opening', 'totalDebit', 'totalCredit', 'closing', 'from', 'to'));
    }

    public function balanceSheet(Request $request)
    {
        $toDate = $request->input('to', now()->format('Y-m-d'));

        $assets = $this->getAccountBalances('asset', $toDate);
        $liabilities = $this->getAccountBalances('liability', $toDate);
        $equityAccounts = $this->getAccountBalances('equity', $toDate);

        $revenue = $this->getAccountBalances('revenue', $toDate);
        $expense = $this->getAccountBalances('expense', $toDate);
        
        $netIncome = $revenue['total'] - $expense['total'];
        $totalEquity = $equityAccounts['total'] + $netIncome;

        $totalAssets = $assets['total'];
        $totalLiabEquity = $liabilities['total'] + $totalEquity;
        $isBalanced = abs($totalAssets - $totalLiabEquity) < 0.01;

        return view('financial.balance_sheet', compact(
            'assets', 'liabilities', 'equityAccounts', 'netIncome', 'totalEquity',
            'totalAssets', 'totalLiabEquity', 'isBalanced', 'toDate'
        ));
    }

    private function getAccountBalances(string $type, ?string $toDate): array
    {
        $accountIds = Account::where('type', $type)->pluck('id');

        $sums = JournalLine::whereIn('account_id', $accountIds)
            ->whereHas('entry', function ($q) use ($toDate) {
                $q->where('is_posted', true);
                if ($toDate) $q->whereDate('entry_date', '<=', $toDate);
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = Account::where('type', $type)->whereIn('id', $sums->keys())->orderBy('code')->get();

        $rows = collect();
        $total = 0;

        foreach ($accounts as $account) {
            $debit = (float) ($sums[$account->id]->debit ?? 0);
            $credit = (float) ($sums[$account->id]->credit ?? 0);

            $balance = in_array($type, ['asset', 'expense']) ? ($debit - $credit) : ($credit - $debit);

            if (abs($balance) < 0.005) continue;

            $rows->push(['account' => $account, 'balance' => $balance]);
            $total += $balance;
        }

        return ['rows' => $rows, 'total' => $total];
    }
}