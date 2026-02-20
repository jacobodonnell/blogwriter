<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class EnsureInstalled
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Skip in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Skip install route itself
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Skip login route (only auth route needed for CLI-only install)
        if ($request->is('login')) {
            return $next($request);
        }

        // Check if installed (lock file exists)
        if (! file_exists(storage_path('installed.lock'))) {
            return redirect('/install');
        }

        return $next($request);
    }
}
