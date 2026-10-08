<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\User;
use App\Services\FundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FundTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_fund_transfer_records_both_account_transactions_and_balances(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cash = Account::create([
            'code' => '1001',
            'name' => 'Cash',
            'type' => 'asset',
            'is_active' => true,
        ]);
        $bank = Account::create([
            'code' => '1002',
            'name' => 'Bank',
            'type' => 'asset',
            'is_active' => true,
        ]);
        $equity = Account::create([
            'code' => '3101',
            'name' => 'Capital',
            'type' => 'equity',
            'is_active' => true,
        ]);

        $openingEntry = JournalEntry::create([
            'entry_no' => 'OPENING-TEST',
            'entry_date' => now()->toDateString(),
            'description' => 'Opening cash balance',
            'is_posted' => true,
            'user_id' => $user->id,
        ]);
        JournalLine::create([
            'journal_entry_id' => $openingEntry->id,
            'account_id' => $cash->id,
            'debit' => 1500,
            'credit' => 0,
        ]);
        JournalLine::create([
            'journal_entry_id' => $openingEntry->id,
            'account_id' => $equity->id,
            'debit' => 0,
            'credit' => 1500,
        ]);

        $transfer = app(FundService::class)->transfer(
            $cash->id,
            $bank->id,
            1000,
            now()->toDateString()
        );

        $this->assertDatabaseCount('fund_transfers', 1);
        $this->assertSame(2, AccountTransaction::where('reference_id', $transfer->id)->count());
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $cash->id,
            'transaction_no' => $transfer->transfer_no,
            'type' => 'transfer_out',
            'amount' => 1000,
        ]);
        $this->assertDatabaseHas('account_transactions', [
            'account_id' => $bank->id,
            'transaction_no' => $transfer->transfer_no,
            'type' => 'transfer_in',
            'amount' => 1000,
        ]);
        $this->assertSame(500.0, app(FundService::class)->getAccountBalance($cash->id));
        $this->assertSame(1000.0, app(FundService::class)->getAccountBalance($bank->id));
        $this->assertTrue(Schema::hasColumn('account_transactions', 'account_id'));
        $this->assertTrue(Schema::hasColumn('account_transactions', 'transaction_date'));
    }
}
