<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosController;

Route::middleware('permission:sales.create')->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('/pos/quick-customer', [PosController::class, 'storeQuickCustomer'])->name('pos.quick-customer');
});