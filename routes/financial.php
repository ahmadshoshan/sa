<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FinancialReportController;

Route::middleware('permission:financial.view')->group(function () {
    Route::get('/financial/trial-balance', [FinancialReportController::class, 'trialBalance'])->name('financial.trial-balance');
    Route::get('/financial/ledger', [FinancialReportController::class, 'ledger'])->name('financial.ledger');
    Route::get('/financial/balance-sheet', [FinancialReportController::class, 'balanceSheet'])->name('financial.balance-sheet');
});