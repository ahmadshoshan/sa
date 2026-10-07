<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatementController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/statements', [StatementController::class, 'index'])->name('statements.index');
    Route::get('/statements/customer/{customer}', [StatementController::class, 'customerStatement'])->name('statements.customer');
    Route::get('/statements/supplier/{supplier}', [StatementController::class, 'supplierStatement'])->name('statements.supplier');
});