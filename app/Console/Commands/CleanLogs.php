<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanLogs extends Command
{
    protected $signature = 'logs:clean {--days=30}';

    protected $description = 'حذف ملفات السجلات القديمة';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $directory = storage_path('logs');

        if (!is_dir($directory)) {
            return Command::SUCCESS;
        }

        $files = File::files($directory);
        $deleted = 0;
        $cutoff = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), '.log') && $file->getMTime() < $cutoff) {
                File::delete($file->getPathname());
                $deleted++;
            }
        }

        $this->info("تم حذف {$deleted} ملف سجل قديم.");

        return Command::SUCCESS;
    }
}