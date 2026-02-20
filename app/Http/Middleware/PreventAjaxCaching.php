<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreventAjaxCaching
{
    /**
     * Prevent browsers from caching Alpine AJAX partial responses as full pages.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->hasHeader('X-Alpine-Target')) {
            $response->headers->set('Cache-Control', 'no-store');
        }

        return $response;
    }
}
