<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\InstallService;
use Closure;
use Illuminate\Http\Request;

final readonly class EnsureInstalled
{
    public function __construct(private InstallService $installService) {}

    public function handle(Request $request, Closure $next): mixed
    {
        // Skip in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        // Always allow the install route through
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Check installed: lock file + healthy database
        if ($this->installService->isAlreadyInstalled()) {
            return $next($request);
        }

        // Not installed — redirect to installer
        return redirect('/install');
    }
}
