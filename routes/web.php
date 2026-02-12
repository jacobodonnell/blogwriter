<?php

use App\Http\Controllers\Admin\AdminPhotoController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::redirect('/blog', '/');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/category/{slug}', [ArticleController::class, 'category'])->name('category.show');
Route::view('/about', 'public.about')->name('about');

// Public photo routes
Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');
Route::get('/photos/{photo:slug}', [PhotoController::class, 'show'])->name('photos.show');

// Admin Routes
// Auth required in production, optional in local/development
$adminMiddleware = app()->environment(['local', 'development', 'dev']) ? [] : ['auth'];

Route::middleware($adminMiddleware)->prefix('admin')->name('admin.')->group(function (): void {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Articles
    Route::resource('articles', AdminArticleController::class);

    // Photos
    Route::resource('photos', AdminPhotoController::class);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'create']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');

    // Private file serving (requires authentication)
    Route::get('/storage/private/{path}', function (string $path) {
        $fullPath = 'articles/featured/'.$path;

        if (! Storage::disk('private')->exists($fullPath)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('private')->path($fullPath),
            [
                'Content-Type' => Storage::disk('private')->mimeType($fullPath),
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    })->where('path', '.*')->name('private.file');
});

// Install route (must be accessible without auth)
Route::get('/install', [InstallController::class, 'index'])->name('install');
