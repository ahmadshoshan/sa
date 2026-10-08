<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupController extends Controller
{
    public function index()
    {
        $directory = storage_path('app/backups');

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $files = collect(File::files($directory))
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            })
            ->sortByDesc('date')
            ->values();

        return view('backups.index', compact('files'));
    }

    public function run()
    {
        try {
            $exitCode = Artisan::call('backup:run');

            if ($exitCode !== 0) {
                $output = trim(Artisan::output());
                throw new \RuntimeException($output !== '' ? $output : 'تعذر إنشاء ملف النسخة الاحتياطية.');
            }

            return redirect()
                ->route('backups.index')
                ->with('success', 'تم إنشاء نسخة احتياطية بنجاح.');
        } catch (Throwable $e) {
            return redirect()
                ->route('backups.index')
                ->with('error', 'فشل إنشاء النسخة الاحتياطية: '.$e->getMessage());
        }
    }

    public function download($file)
    {
        $file = basename($file);
        $path = storage_path('app/backups/'.$file);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function destroy($file)
    {
        $file = basename($file);
        $path = storage_path('app/backups/'.$file);

        if (file_exists($path)) {
            unlink($path);
        }

        return redirect()
            ->route('backups.index')
            ->with('success', 'تم حذف النسخة الاحتياطية.');
    }
}
