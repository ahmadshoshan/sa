<?php

namespace App\Console\Commands;

use App\Services\ResetService;
use Illuminate\Console\Command;

class ResetSystem extends Command
{
    protected $signature = 'reset:system {level=transactions}';

    protected $description = 'تفريغ النظام وإعادة إعداده';

    public function handle(ResetService $resetService): int
    {
        $level = $this->argument('level');

        if (!in_array($level, ['transactions', 'all', 'factory'])) {
            $this->error('مستوى غير صالح. استخدم: transactions, all, factory');
            return Command::FAILURE;
        }

        if (!$this->confirm('هل أنت متأكد من تفريغ النظام؟ لا يمكن التراجع عن هذه العملية.')) {
            $this->info('تم إلغاء العملية.');
            return Command::SUCCESS;
        }

        match ($level) {
            'transactions' => $resetService->resetTransactions(),
            'all' => $resetService->resetAllKeepSettings(),
            'factory' => $resetService->factoryReset(),
        };

        $this->info('تم تفريغ النظام بنجاح.');

        return Command::SUCCESS;
    }
}