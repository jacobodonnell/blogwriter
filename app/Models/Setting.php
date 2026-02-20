<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class Setting extends Model
{
    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'setting.'.$key;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $value = self::query()->where('key', $key)->value('value');

        if ($value !== null) {
            Cache::put($cacheKey, $value, now()->addHour());
        }

        return $value ?? $default;
    }

    /**
     * Set a setting value by key (upsert).
     */
    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget('setting.'.$key);
    }

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }
}
