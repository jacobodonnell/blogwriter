<?php

declare(strict_types=1);

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryContentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Articles
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

// Categories
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{path}', [CategoryContentController::class, 'index'])
    ->where('path', '.*')
    ->name('categories.show');

// Photos
Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');
Route::get('/photos/{photo:slug}', [PhotoController::class, 'show'])->name('photos.show');

// About
Route::get('/about', AboutController::class)->name('about');
