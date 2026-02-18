<?php

use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ArticlePreviewController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CreateArticleController;
use App\Http\Controllers\Admin\CreateArticlePreviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Articles — new article flow (session-based, no DB until first save)
    Route::get('/articles/create', [CreateArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles/preview', CreateArticlePreviewController::class)->name('articles.preview.store');
    Route::post('/articles', [CreateArticleController::class, 'store'])->name('articles.store');

    // Articles — existing articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::match(['post', 'put'], '/articles/{article}/preview', ArticlePreviewController::class)->name('articles.preview.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

    // Photos
    Route::get('/photos', [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::get('/photos/create', [AdminPhotoController::class, 'create'])->name('photos.create');
    Route::post('/photos', [AdminPhotoController::class, 'store'])->name('photos.store');
    Route::get('/photos/{photo}', [AdminPhotoController::class, 'show'])->name('photos.show');
    Route::get('/photos/{photo}/edit', [AdminPhotoController::class, 'edit'])->name('photos.edit');
    Route::put('/photos/{photo}', [AdminPhotoController::class, 'update'])->name('photos.update');
    Route::delete('/photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');

    // Categories (no show or create routes)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [SettingsController::class, 'update'])->name('settings.profile.update');
    Route::put('/settings/placeholder-image', [SettingsController::class, 'updatePlaceholderImage'])->name('settings.placeholder-image.update');

    // Page Settings
    Route::put('/settings/pages', [PageSettingsController::class, 'update'])->name('settings.pages.update');

    // Appearance
    Route::get('/settings/appearance', [AppearanceController::class, 'index'])->name('settings.appearance');
    Route::put('/settings/appearance', [AppearanceController::class, 'update'])->name('settings.appearance.update');

    // Private media file serving
    Route::get('/media/{media}/{conversion?}', [MediaController::class, 'show'])->name('media.show');
});
