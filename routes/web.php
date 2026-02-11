<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::redirect('/blog', '/');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/category/{slug}', [ArticleController::class, 'category'])->name('category.show');
Route::view('/about', 'public.about')->name('about');

// Admin Routes
// Auth required in production, optional in local/development
$adminMiddleware = app()->environment(['local', 'development', 'dev']) ? [] : ['auth'];

Route::middleware($adminMiddleware)->prefix('admin')->name('admin.')->group(function (): void {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Articles
    Route::resource('articles', AdminArticleController::class);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
});
