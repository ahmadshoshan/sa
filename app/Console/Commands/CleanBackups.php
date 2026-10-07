<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanBackups extends Command
{
    protected $signature = 'backup:clean {--days=30}';

    protected $description = 'حذف النسخ الاحتياطية القديمة';

    public function handle(): int
    {
        $days = (int) $this->option('days') ?: (int) env('BACKUP_RETENTION_DAYS', 30);
        $directory = storage_path('app/backups');

        if (!is_dir($directory)) {
            $this->info('لا توجد نسخ احتياطية.');
            return Command::SUCCESS;
        }

        $files = File::files($directory);
        $deleted = 0;
        $cutoff = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $this->info('حُذفت: ' . $file->getFilename());
                $deleted++;
            }
        }

        $this->info("تم حذف {$deleted} نسخة احتياطية قديمة.");

        return Command::SUCCESS;
    }
}