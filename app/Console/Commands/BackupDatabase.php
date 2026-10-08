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
        $temporaryFiles = [];

        try {
            $filename = $this->option('file') ?: 'backup_'.now()->format('Y_m_d_His').'.sql';
            $directory = storage_path('app/backups');

            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $filename = basename($filename);
            if ($filename === '' || strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'sql') {
                throw new \InvalidArgumentException('اسم ملف النسخة الاحتياطية يجب أن ينتهي بامتداد .sql.');
            }

            $destination = $directory.DIRECTORY_SEPARATOR.$filename;
            $temporaryFiles = [
                $destination.'.tmp',
                $destination.'.error',
            ];

            $connection = config('database.default');
            if (! in_array($connection, ['mysql', 'mariadb'], true)) {
                throw new \RuntimeException("النسخ الاحتياطي عبر mysqldump غير مدعوم لاتصال قاعدة البيانات: {$connection}.");
            }

            $config = config("database.connections.{$connection}");
            $binary = $this->resolveDumpBinary();
            $command = [
                $binary,
                '--host='.$config['host'],
                '--port='.$config['port'],
                '--user='.$config['username'],
                '--single-transaction',
                '--quick',
                '--lock-tables=false',
            ];

            if (! empty($config['password'])) {
                $command[] = '--password='.$config['password'];
            }

            $command[] = $config['database'];

            $process = proc_open(
                $command,
                [
                    0 => ['pipe', 'r'],
                    1 => ['file', $temporaryFiles[0], 'w'],
                    2 => ['file', $temporaryFiles[1], 'w'],
                ],
                $pipes,
                base_path()
            );

            if (! is_resource($process)) {
                throw new \RuntimeException('تعذر تشغيل mysqldump. تحقق من تثبيت الأداة أو قيمة DB_DUMP_PATH.');
            }

            fclose($pipes[0]);
            $exitCode = proc_close($process);
            $errorOutput = trim((string) file_get_contents($temporaryFiles[1]));

            if ($exitCode !== 0) {
                throw new \RuntimeException(
                    'فشل mysqldump برمز '.$exitCode.($errorOutput !== '' ? ': '.$errorOutput : '.')
                );
            }

            if (! is_file($temporaryFiles[0]) || filesize($temporaryFiles[0]) === 0) {
                throw new \RuntimeException('اكتمل mysqldump دون إنشاء ملف بيانات؛ لم تُحفظ نسخة احتياطية.');
            }

            if (! rename($temporaryFiles[0], $destination)) {
                throw new \RuntimeException('تعذر حفظ ملف النسخة الاحتياطية في مجلد التخزين.');
            }

            $this->info("تم إنشاء النسخة الاحتياطية: {$destination}");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return Command::FAILURE;
        } finally {
            foreach ($temporaryFiles as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    unlink($temporaryFile);
                }
            }
        }
    }

    private function resolveDumpBinary(): string
    {
        $configuredBinary = env('DB_DUMP_PATH');
        if ($configuredBinary) {
            if (! is_file($configuredBinary)) {
                throw new \RuntimeException('المسار المحدد في DB_DUMP_PATH غير موجود: '.$configuredBinary);
            }

            return $configuredBinary;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $xamppBinary = dirname(base_path(), 2).DIRECTORY_SEPARATOR.'mysql'
                .DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mysqldump.exe';

            if (is_file($xamppBinary)) {
                return $xamppBinary;
            }
        }

        return 'mysqldump';
    }
}
