<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeReportController;

Route::middleware('permission:financial.view')->group(function () {
    Route::get('/financial/income-statement', [IncomeReportController::class, 'incomeStatement'])->name('financial.income-statement');
    Route::get('/financial/revenues', [IncomeReportController::class, 'revenues'])->name('financial.revenues');
    Route::get('/financial/expenses', [IncomeReportController::class, 'expenses'])->name('financial.expenses');
});