<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantGuideController;

Route::middleware('auth')->group(function () {
    Route::get('/assistant-guide', [AssistantGuideController::class, 'index'])->name('assistant-guide.index');
});