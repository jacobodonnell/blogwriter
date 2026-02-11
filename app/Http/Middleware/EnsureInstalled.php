<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureInstalled
{
    public function handle(Request $request, Closure $next)
    {
        // Skip in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Skip install route itself
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Skip auth routes (login, register, password reset)
        if ($request->is('login') || $request->is('register') || $request->is('password/*')) {
            return $next($request);
        }

        // Check if installed (lock file exists)
        if (! file_exists(storage_path('installed.lock'))) {
            return redirect('/install');
        }

        return $next($request);
    }
}
