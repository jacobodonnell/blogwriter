<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\ResponseCache\Facades\ResponseCache;

trait InvalidatesResponseCache
{
    public static function bootInvalidatesResponseCache(): void
    {
        static::saved(fn () => ResponseCache::clear());
        static::deleted(fn () => ResponseCache::clear());
    }
}
