<?php

use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// Public Routes (minimal for now)
Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Articles
    Route::resource('articles', ArticleController::class);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);
});
