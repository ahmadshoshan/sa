<?php
namespace Database\Seeders;
use App\Models\Account;
use Illuminate\Database\Seeder;

class FundAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1003', 'name' => 'الكاش / الإنستا', 'type' => 'asset', 'is_active' => true],
            ['code' => '1004', 'name' => 'الكاش في الصندوق', 'type' => 'asset', 'is_active' => true],
        ];
        foreach ($accounts as $account) {
            Account::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}