<?php

use Illuminate\Support\Facades\Route;
use Easyagency\LaravelAdmin\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::any('/cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');
