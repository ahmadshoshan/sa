<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeDatabase extends Command
{
    protected $signature = 'db:optimize';

    protected $description = 'تحسين جداول قاعدة البيانات';

    public function handle(): int
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $optimized = 0;

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $dbName};

            try {
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
                $this->info("تم تحسين: {$tableName}");
                $optimized++;
            } catch (\Throwable $e) {
                $this->warn("خطأ في {$tableName}: " . $e->getMessage());
            }
        }

        $this->info("تم تحسين {$optimized} جدول.");

        return Command::SUCCESS;
    }
}