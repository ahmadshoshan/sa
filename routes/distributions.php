<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfitDistributionController;

Route::middleware('auth')->group(function () {

    Route::middleware('permission:distributions.manage')->group(function () {

        Route::get('/profit-distributions/create', [ProfitDistributionController::class, 'create'])->name('distributions.create');
        Route::post('/profit-distributions', [ProfitDistributionController::class, 'store'])->name('distributions.store');
        Route::post('/profit-distributions/{distribution}/approve', [ProfitDistributionController::class, 'approve'])->name('distributions.approve');

        Route::get('/profit-distributions/{distribution}', [ProfitDistributionController::class, 'show'])->name('distributions.show');
        Route::get('/profit-distributions', [ProfitDistributionController::class, 'index'])->name('distributions.index');

        Route::post('/profit-distribution-items/{item}/pay', [ProfitDistributionController::class, 'payItem'])->name('distributions.items.pay');
    });
});