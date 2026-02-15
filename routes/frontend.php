<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryArticleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Articles
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::redirect('/blog', '/');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('article.show');

// Categories
Route::get('/category/{slug}', [CategoryArticleController::class, 'index'])->name('category.show');

// Photos
Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');
Route::get('/photos/{photo:slug}', [PhotoController::class, 'show'])->name('photos.show');

// Profile
Route::get('/profile', fn () => view('public.profile', [
    'user' => \App\Models\User::first(),
]))->name('profile');
