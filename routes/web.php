<?php

use HasinHayder\TyroDashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');

Route::get('/clear-all-cache', function() {
    Artisan::call('optimize:clear');
    return 'All caches cleared successfully!';
});

// Route::middleware('auth')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');
// });
