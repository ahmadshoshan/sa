<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintController;

// Sales printing
Route::middleware('permission:sales.view')->group(function () {
    Route::get('/sales/{invoice}/print/a4', [PrintController::class, 'saleA4'])->name('sales.print.a4');
    Route::get('/sales/{invoice}/print/thermal', [PrintController::class, 'saleThermal'])->name('sales.print.thermal');
});

// Purchases printing
Route::middleware('permission:purchases.view')->group(function () {
    Route::get('/purchases/{invoice}/print/a4', [PrintController::class, 'purchaseA4'])->name('purchases.print.a4');
});

// Returns printing
Route::middleware('permission:returns.view')->group(function () {
    Route::get('/sales-returns/{invoice}/print/a4', [PrintController::class, 'saleReturnA4'])->name('sales-returns.print.a4');
    Route::get('/purchase-returns/{invoice}/print/a4', [PrintController::class, 'purchaseReturnA4'])->name('purchase-returns.print.a4');
});

// Payments printing
Route::middleware('permission:payments.view')->group(function () {
    Route::get('/payments/{payment}/print', [PrintController::class, 'paymentVoucher'])->name('payments.print');
});