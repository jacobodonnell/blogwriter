<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

final class ResponseCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        return $request->isMethod('GET') && ! $request->user();
    }

    public function shouldServeAsCache(Request $request): bool
    {
        return ! $request->user();
    }
}
