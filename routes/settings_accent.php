<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::middleware('auth')->group(function () {
    Route::post('/settings/accent', [SettingsController::class, 'saveAccent'])->name('settings.accent');
});