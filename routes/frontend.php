<?php

declare(strict_types=1);

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\RobotsController;
use Illuminate\Support\Facades\Route;
use Spatie\ResponseCache\Middlewares\CacheResponse;

Route::middleware(CacheResponse::class)->group(function (): void {
    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Articles
    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

    // Photos
    Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/photos/{photo:slug}', [PhotoController::class, 'show'])->name('photos.show');

    // About
    Route::get('/about', AboutController::class)->name('about');

    // Feeds
    Route::get('/feed', [FeedController::class, 'rss'])->name('feed.rss');
    Route::get('/rss', [FeedController::class, 'rss'])->name('feed.rss.alias');
    Route::get('/atom', [FeedController::class, 'atom'])->name('feed.atom');
    Route::get('/feed.json', [FeedController::class, 'json'])->name('feed.json');

    // Robots
    Route::get('/robots.txt', RobotsController::class)->name('robots.txt');
});
