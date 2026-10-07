<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/user-guide', function () {
        return view('guide.index');
    })->name('guide.index');
});