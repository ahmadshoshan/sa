<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--file=}';

    protected $description = 'إنشاء نسخة احتياطية من قاعدة البيانات MySQL';

    public function handle(): int
    {
        try {
            $filename = $this->option('file') ?: 'backup_' . now()->format('Y_m_d_His') . '.sql';

            $directory = storage_path('app/backups');

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $destination = $directory . '/' . $filename;

            $config = config('database.connections.mysql');

            $binary = env('DB_DUMP_PATH', 'mysqldump');

            $commandParts = [
                escapeshellarg($binary),
                '--host=' . escapeshellarg($config['host']),
                '--port=' . escapeshellarg($config['port']),
                '--user=' . escapeshellarg($config['username']),
                '--single-transaction',
                '--quick',
                '--lock-tables=false',
                escapeshellarg($config['database']),
            ];

            if (!empty($config['password'])) {
                $commandParts[] = '--password=' . escapeshellarg($config['password']);
            }

            $command = implode(' ', $commandParts) . ' > ' . escapeshellarg($destination);

            exec($command, $output, $exitCode);

            if ($exitCode !== 0 || !file_exists($destination) || filesize($destination) === 0) {
                throw new \Exception('فشل تنفيذ أمر mysqldump. تحقق من مسار DB_DUMP_PATH في ملف .env');
            }

            $this->info("تم إنشاء النسخة الاحتياطية: {$destination}");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}