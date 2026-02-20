<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Ensure app is installed before serving requests
        $middleware->append(App\Http\Middleware\EnsureInstalled::class);

        // Prevent browsers from caching Alpine AJAX partial responses
        $middleware->append(App\Http\Middleware\PreventAjaxCaching::class);

        // Exclude install routes from CSRF — finalize clears caches which
        // invalidates the session, making the token stale for the seed request.
        // These routes are already guarded by isAlreadyInstalled() checks.
        $middleware->validateCsrfTokens(except: [
            'install/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
