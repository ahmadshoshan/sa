<?php
namespace App\Services;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\FundTransfer;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class FundService
{
    public function transfer(int $fromAccountId, int $toAccountId, float $amount, string $date, ?string $notes = null): FundTransfer
    {
        return DB::transaction(function () use ($fromAccountId, $toAccountId, $amount, $date, $notes) {
            if ($amount <= 0) throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
            if ($fromAccountId === $toAccountId) throw new Exception('لا يمكن التحويل لنفس الحساب');

            $fromAccount = Account::find($fromAccountId);
            $toAccount = Account::find($toAccountId);
            if (!$fromAccount || !$toAccount) throw new Exception('الحساب غير موجود');

            $balance = $this->getAccountBalance($fromAccountId);
            if ($balance < $amount) {
                throw new Exception("الرصيد غير كافي في {$fromAccount->name}. الرصيد الحالي: " . number_format($balance, 2));
            }

            $lastId = FundTransfer::max('id') ?? 0;
            $transferNo = 'TRF-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            $transfer = FundTransfer::create([
                'transfer_no' => $transferNo,
                'from_account_id' => $fromAccountId,
                'to_account_id' => $toAccountId,
                'amount' => $amount,
                'transfer_date' => $date,
                'notes' => $notes,
                'user_id' =>  Auth::id(),
            ]);

            $this->createTransferJournal($transfer);

            AccountTransaction::create([
                'account_id' => $fromAccountId, 'transaction_no' => $transfer->transfer_no,
                'type' => 'transfer_out', 'amount' => $amount, 'transaction_date' => $date,
                'reference_type' => 'transfer', 'reference_id' => $transfer->id,
                'notes' => $notes, 'user_id' =>  Auth::id(),
            ]);

            AccountTransaction::create([
                'account_id' => $toAccountId, 'transaction_no' => $transfer->transfer_no,
                'type' => 'transfer_in', 'amount' => $amount, 'transaction_date' => $date,
                'reference_type' => 'transfer', 'reference_id' => $transfer->id,
                'notes' => $notes, 'user_id' =>  Auth::id(),
            ]);

            return $transfer;
        });
    }

    protected function createTransferJournal(FundTransfer $transfer): JournalEntry
    {
        $lastId = JournalEntry::max('id') ?? 0;
        $journal = JournalEntry::create([
            'entry_no' => 'JE-' . now()->format('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT),
            'entry_date' => $transfer->transfer_date,
            'description' => "تحويل نقدي من {$transfer->fromAccount->name} إلى {$transfer->toAccount->name}",
            'ref_type' => 'transfer', 'ref_id' => $transfer->id,
            'is_posted' => true, 'user_id' =>  Auth::id(),
        ]);

        JournalLine::create([
            'journal_entry_id' => $journal->id, 'account_id' => $transfer->to_account_id,
            'debit' => $transfer->amount, 'credit' => 0,
        ]);
        JournalLine::create([
            'journal_entry_id' => $journal->id, 'account_id' => $transfer->from_account_id,
            'debit' => 0, 'credit' => $transfer->amount,
        ]);

        return $journal;
    }

    public function getAccountBalance(int $accountId): float
    {
        $account = Account::find($accountId);
        if (!$account) return 0;
        $debit = JournalLine::where('account_id', $accountId)->sum('debit');
        $credit = JournalLine::where('account_id', $accountId)->sum('credit');
        return in_array($account->type, ['asset', 'expense']) ? (float)($debit - $credit) : (float)($credit - $debit);
    }

    public function getMainAccountsBalances(): array
    {
        return [
            'cash' => $this->getBalanceByCode('1001'),
            'bank' => $this->getBalanceByCode('1002'),
            'insta' => $this->getBalanceByCode('1003'),
            'cashbox' => $this->getBalanceByCode('1004'),
            'receivables' => (float)Customer::sum('current_balance'),
            'payables' => (float)Supplier::sum('current_balance'),
        ];
    }

    protected function getBalanceByCode(string $code): float
    {
        $account = Account::where('code', $code)->first();
        return $account ? $this->getAccountBalance($account->id) : 0;
    }

    public function getTransfers(int $limit = 50)
    {
        return FundTransfer::with(['fromAccount', 'toAccount', 'user'])->latest('transfer_date')->limit($limit)->get();
    }

    public function getAccountTransactions(int $accountId, int $limit = 50)
    {
        return AccountTransaction::where('account_id', $accountId)->latest('transaction_date')->limit($limit)->get();
    }
}