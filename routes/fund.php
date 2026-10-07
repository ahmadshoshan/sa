<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FundController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/fund', [FundController::class, 'index'])->name('fund.index');
    Route::get('/fund/transfer', [FundController::class, 'transferForm'])->name('fund.transfer-form');
    Route::post('/fund/transfer', [FundController::class, 'storeTransfer'])->name('fund.store-transfer');
    Route::get('/fund/accounts/{id}', [FundController::class, 'accountDetails'])->name('fund.account-details');
});