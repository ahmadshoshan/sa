<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;

Route::middleware('auth')->group(function () {

    Route::middleware('permission:sales.view')->group(function () {
        Route::get('/sales/{invoice}/installments', [InstallmentController::class, 'show'])->name('sales.installments');
    });

    Route::middleware('permission:sales.create')->group(function () {
        Route::post('/sales/{invoice}/installments/generate', [InstallmentController::class, 'generate'])->name('sales.installments.generate');
        Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay'])->name('installments.pay');
    });
});