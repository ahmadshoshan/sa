<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResetController;

Route::middleware('auth')->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('/reset', [ResetController::class, 'index'])->name('reset.index');
        Route::get('/reset/{type}', [ResetController::class, 'confirm'])->name('reset.confirm');
        Route::post('/reset/{type}', [ResetController::class, 'execute'])->name('reset.execute');
    });
});