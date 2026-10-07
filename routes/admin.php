<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AuditLogController;

Route::middleware('permission:settings.manage')->group(function () {

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups/run', [BackupController::class, 'run'])->name('backups.run');
    Route::get('/backups/download/{file}', [BackupController::class, 'download'])->name('backups.download');
    Route::delete('/backups/{file}', [BackupController::class, 'destroy'])->name('backups.destroy');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/deployment-checklist', function () {
        return view('admin.deployment');
    })->name('deployment.checklist');
});
