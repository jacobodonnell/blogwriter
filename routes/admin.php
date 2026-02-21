<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminCategoryChildrenController;
use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\AppearanceController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ArticlePreviewController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryExploreController;
use App\Http\Controllers\Admin\CreateArticleController;
use App\Http\Controllers\Admin\CreateArticlePreviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PlaceholderImageController;
use App\Http\Controllers\Admin\ProfileSettingsController;
use App\Http\Controllers\Admin\SiteSettingsController;
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

    // Category explore — filesystem browser view
    Route::get('/categories/explore', [CategoryExploreController::class, 'index'])
        ->name('categories.explore');
    Route::get('/categories/explore/{path}', [CategoryExploreController::class, 'show'])
        ->where('path', '.*')
        ->name('categories.explore.show');

    // Category children — slug-path index (must be last among category GET routes)
    Route::get('/categories/{path}', [AdminCategoryChildrenController::class, 'index'])
        ->where('path', '.*')
        ->name('categories.children');

    // Settings — redirect index to first tab
    Route::redirect('/settings', '/admin/settings/profile')->name('settings');

    // Profile
    Route::get('/settings/profile', [ProfileSettingsController::class, 'edit'])->name('settings.profile');
    Route::put('/settings/profile', [ProfileSettingsController::class, 'update'])->name('settings.profile.update');

    // Site
    Route::get('/settings/site', [SiteSettingsController::class, 'edit'])->name('settings.site');
    Route::put('/settings/site', [SiteSettingsController::class, 'update'])->name('settings.site.update');
    Route::put('/settings/site/image', [PlaceholderImageController::class, 'update'])->name('settings.site.image.update');

    // Appearance
    Route::get('/settings/appearance', [AppearanceController::class, 'edit'])->name('settings.appearance');
    Route::put('/settings/appearance', [AppearanceController::class, 'update'])->name('settings.appearance.update');

    // Private media file serving
    Route::get('/media/{media}/{conversion?}', [MediaController::class, 'show'])->name('media.show');
});
