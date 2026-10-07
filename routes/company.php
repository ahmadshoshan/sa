<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PartnerCapitalController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PartnerWithdrawalController;

Route::middleware('auth')->group(function () {

    // Expense categories
    Route::middleware('permission:partners.manage')->group(function () {
        Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    });

    // Partners
    Route::middleware('permission:partners.manage')->group(function () {
        Route::get('/partners/create', [PartnerController::class, 'create'])->name('partners.create');
        Route::post('/partners', [PartnerController::class, 'store'])->name('partners.store');
        Route::get('/partners/{partner}/edit', [PartnerController::class, 'edit'])->name('partners.edit');
        Route::put('/partners/{partner}', [PartnerController::class, 'update'])->name('partners.update');
        Route::delete('/partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy');
        Route::get('/partners/{partner}', [PartnerController::class, 'show'])->name('partners.show');
        Route::get('/partners', [PartnerController::class, 'index'])->name('partners.index');
    });

    // Partner capital and withdrawals
    Route::middleware('permission:capital.manage')->group(function () {
        Route::get('/partners/{partner}/capitals/create', [PartnerCapitalController::class, 'create'])->name('partners.capitals.create');
        Route::post('/partners/{partner}/capitals', [PartnerCapitalController::class, 'store'])->name('partners.capitals.store');

        Route::get('/partners/{partner}/withdrawals/create', [PartnerWithdrawalController::class, 'create'])->name('partners.withdrawals.create');
        Route::post('/partners/{partner}/withdrawals', [PartnerWithdrawalController::class, 'store'])->name('partners.withdrawals.store');
    });

    // Expenses
    Route::middleware('permission:expenses.create')->group(function () {
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    });

    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    });
});