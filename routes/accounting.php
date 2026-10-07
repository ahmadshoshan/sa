<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JournalEntryController;

Route::middleware('permission:financial.view')->group(function () {
    Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal.index');
    Route::get('/journal-entries/{journalEntry}', [JournalEntryController::class, 'show'])->name('journal.show');
});