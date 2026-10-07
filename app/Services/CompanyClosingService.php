<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalLine;
use App\Models\PeriodClosing;
use Illuminate\Support\Facades\Auth;

class CompanyClosingService
{
    public function __construct(
        protected JournalPostingService $journal
    ) {}

    public function getNetProfit(string $from, string $to): float
    {
        $revenueAccountIds = Account::where('type', 'revenue')->pluck('id');
        $expenseAccountIds = Account::where('type', 'expense')->pluck('id');

        $revenueBalance = $this->creditMinusDebit($revenueAccountIds, $from, $to);
        $expenseBalance = -$this->creditMinusDebit($expenseAccountIds, $from, $to);

        return round($revenueBalance - $expenseBalance, 2);
    }

    public function closePeriod(string $from, string $to): float
    {
        $existing = PeriodClosing::where('period_from', $from)
            ->where('period_to', $to)
            ->first();

        if ($existing) {
            return (float) $existing->net_profit;
        }

        $lines = [];

        $totalRevenue = 0;
        $totalExpense = 0;

        $revenueAccounts = Account::where('type', 'revenue')->get();
        $expenseAccounts = Account::where('type', 'expense')->get();

        foreach ($revenueAccounts as $account) {
            $balance = $this->creditMinusDebit(collect([$account->id]), $from, $to);

            if (abs($balance) < 0.005) {
                continue;
            }

            if ($balance > 0) {
                $lines[] = [
                    'account_code' => $account->code,
                    'debit' => $balance,
                    'credit' => 0,
                ];
            } else {
                $lines[] = [
                    'account_code' => $account->code,
                    'debit' => 0,
                    'credit' => abs($balance),
                ];
            }

            $totalRevenue += $balance;
        }

        foreach ($expenseAccounts as $account) {
            $creditMinusDebit = $this->creditMinusDebit(collect([$account->id]), $from, $to);
            $balance = -$creditMinusDebit;

            if (abs($balance) < 0.005) {
                continue;
            }

            if ($balance > 0) {
                $lines[] = [
                    'account_code' => $account->code,
                    'debit' => 0,
                    'credit' => $balance,
                ];
            } else {
                $lines[] = [
                    'account_code' => $account->code,
                    'debit' => abs($balance),
                    'credit' => 0,
                ];
            }

            $totalExpense += $balance;
        }

        $netProfit = round($totalRevenue - $totalExpense, 2);

        if ($netProfit > 0) {
            $lines[] = [
                'account_code' => '3103',
                'debit' => 0,
                'credit' => $netProfit,
            ];
        } elseif ($netProfit < 0) {
            $lines[] = [
                'account_code' => '3103',
                'debit' => abs($netProfit),
                'credit' => 0,
            ];
        }

        $closing = PeriodClosing::create([
            'period_from' => $from,
            'period_to' => $to,
            'net_profit' => $netProfit,
            'user_id' =>  Auth::id(),
        ]);

        $this->journal->createJournal(
            $to,
            "إغلاق الفترة من {$from} إلى {$to}",
            'period_closing',
            $closing->id,
            $lines
        );

        return $netProfit;
    }

    private function creditMinusDebit($accountIds, string $from, string $to): float
    {
        if (count($accountIds) === 0) {
            return 0;
        }

        $value = JournalLine::whereIn('account_id', $accountIds)
            ->whereHas('entry', function ($query) use ($from, $to) {
                $query->where('is_posted', true)
                    ->whereDate('entry_date', '>=', $from)
                    ->whereDate('entry_date', '<=', $to);
            })
            ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as balance')
            ->value('balance');

        return (float) $value;
    }
}