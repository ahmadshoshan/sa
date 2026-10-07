<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyReportController;

Route::middleware('auth')->group(function () {

    Route::middleware('permission:partners.manage')->group(function () {
        Route::get('/partners/{partner}/statement', [CompanyReportController::class, 'partnerStatement'])->name('partners.statement');
        Route::get('/reports/partners', [CompanyReportController::class, 'partnersSummary'])->name('reports.partners');
    });

    Route::middleware('permission:distributions.manage')->group(function () {
        Route::get('/reports/profit-distributions', [CompanyReportController::class, 'distributionsSummary'])->name('reports.distributions');
        Route::get('/reports/profit-distribution-items', [CompanyReportController::class, 'distributionItems'])->name('reports.distributions-items');
    });
});