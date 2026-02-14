<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::get('/install', [InstallController::class, 'index'])->name('install');
Route::post('/install/check', [InstallController::class, 'check'])->name('install.check');
Route::post('/install/account', [InstallController::class, 'account'])->name('install.account');
Route::post('/install/finalize', [InstallController::class, 'finalize'])->name('install.finalize');
Route::post('/install/seed', [InstallController::class, 'seed'])->name('install.seed');
Route::post('/install/passphrase', [InstallController::class, 'passphrase'])->name('install.passphrase');
