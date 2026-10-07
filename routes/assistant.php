<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;

Route::middleware('auth')->group(function () {
    Route::post('/assistant/ask', [AssistantController::class, 'ask'])->name('assistant.ask');
});