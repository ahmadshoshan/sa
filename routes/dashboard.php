<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function () {
    Route::middleware('permission:reports.view')->group(function () {
        // Name kept as 'dashboard' for compatibility with controller redirects and views.
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});