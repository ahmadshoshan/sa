<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdvancedReportController;

Route::middleware('permission:reports.view')->group(function () {
    Route::get('/reports-advanced/sales-by-product', [AdvancedReportController::class, 'salesByProduct'])->name('reports.sales-by-product');
    Route::get('/reports-advanced/slow-moving', [AdvancedReportController::class, 'slowMoving'])->name('reports.slow-moving');
    Route::get('/reports-advanced/receivables-ageing', [AdvancedReportController::class, 'receivablesAgeing'])->name('reports.receivables-ageing');
    Route::get('/reports-advanced/top-customers', [AdvancedReportController::class, 'topCustomers'])->name('reports.top-customers');
});