<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;

Route::middleware('auth')->group(function () {

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Payments / vouchers
    Route::middleware('permission:payments.view')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    });

    Route::middleware('permission:payments.create')->group(function () {
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    });

    // Barcode labels
    Route::middleware('permission:master-data.manage')->group(function () {
        Route::get('/barcode-labels', [BarcodeController::class, 'index'])->name('barcode.index');
    });
});