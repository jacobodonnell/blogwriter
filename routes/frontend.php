<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryArticleController;
use App\Http\Controllers\PhotoController;
use Illuminate\Support\Facades\Route;

// Home & Blog
Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::redirect('/blog', '/');
Route::get('/blog/{slug}', [ArticleController::class, 'show'])->name('article.show');

// Categories
Route::get('/category/{slug}', [CategoryArticleController::class, 'index'])->name('category.show');

// Photos
Route::get('/photos', [PhotoController::class, 'index'])->name('photos.index');
Route::get('/photos/{photo:slug}', [PhotoController::class, 'show'])->name('photos.show');

// Static Pages
Route::view('/about', 'public.about')->name('about');
