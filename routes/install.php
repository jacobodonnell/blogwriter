<?php

use App\Http\Controllers\Install\InstallAccountController;
use App\Http\Controllers\Install\InstallFinalizationController;
use App\Http\Controllers\Install\InstallPassphraseController;
use App\Http\Controllers\Install\InstallRequirementController;
use App\Http\Controllers\Install\InstallSeedController;
use App\Http\Controllers\InstallController;
use App\Http\Middleware\EnsureWebInstallerEnabled;
use Illuminate\Support\Facades\Route;

Route::middleware(EnsureWebInstallerEnabled::class)->group(function (): void {
    Route::get('/install', [InstallController::class, 'index'])->name('install');
    Route::post('/install/check', [InstallRequirementController::class, 'store'])->name('install.check');
    Route::post('/install/account', [InstallAccountController::class, 'store'])->name('install.account');
    Route::post('/install/finalize', [InstallFinalizationController::class, 'store'])->name('install.finalize');
    Route::post('/install/seed', [InstallSeedController::class, 'store'])->name('install.seed');
    Route::post('/install/passphrase', [InstallPassphraseController::class, 'store'])->name('install.passphrase');
});
