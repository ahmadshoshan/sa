<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalLine;
use Illuminate\Http\Request;

class IncomeReportController extends Controller
{
    public function incomeStatement(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $revenue = $this->accountTotals('revenue', $request);
        $expense = $this->accountTotals('expense', $request);

        $netProfit = $revenue['total'] - $expense['total'];

        return view('financial.income_statement', [
            'revenueRows' => $revenue['rows'],
            'expenseRows' => $expense['rows'],
            'totalRevenue' => $revenue['total'],
            'totalExpense' => $expense['total'],
            'netProfit' => $netProfit,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function revenues(Request $request)
    {
        $data = $this->accountTotals('revenue', $request);

        return view('financial.revenues', [
            'rows' => $data['rows'],
            'total' => $data['total'],
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);
    }

    public function expenses(Request $request)
    {
        $data = $this->accountTotals('expense', $request);

        return view('financial.expenses', [
            'rows' => $data['rows'],
            'total' => $data['total'],
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ]);
    }

    private function accountTotals(string $type, Request $request): array
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $accountIds = Account::where('type', $type)->pluck('id');

        $sums = JournalLine::selectRaw('account_id, COALESCE(SUM(debit), 0) as debit, COALESCE(SUM(credit), 0) as credit')
            ->whereIn('account_id', $accountIds)
            ->whereHas('entry', function ($query) use ($from, $to) {
                $query->where('is_posted', true);

                if ($from) {
                    $query->whereDate('entry_date', '>=', $from);
                }

                if ($to) {
                    $query->whereDate('entry_date', '<=', $to);
                }
            })
            ->groupBy('account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = Account::where('type', $type)
            ->whereIn('id', $sums->keys())
            ->orderBy('code')
            ->get();

        $rows = collect();
        $total = 0;

        foreach ($accounts as $account) {
            $debit = (float) ($sums[$account->id]->debit ?? 0);
            $credit = (float) ($sums[$account->id]->credit ?? 0);

            if ($type === 'revenue') {
                $amount = $credit - $debit;
            } else {
                $amount = $debit - $credit;
            }

            if (abs($debit) < 0.005 && abs($credit) < 0.005 && abs($amount) < 0.005) {
                continue;
            }

            $rows->push([
                'account' => $account,
                'debit' => $debit,
                'credit' => $credit,
                'amount' => $amount,
            ]);

            $total += $amount;
        }

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }
}