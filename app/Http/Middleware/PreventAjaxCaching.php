<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAjaxCaching
{
    /**
     * Prevent browsers from caching Alpine AJAX partial responses as full pages.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
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
