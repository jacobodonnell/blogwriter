<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /**
     * Get a setting value by key.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('placeholder_image_url')) {
    /**
     * Get the public URL for the site placeholder image.
     */
    function placeholder_image_url(): ?string
    {
        $path = setting('site_placeholder_image');

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }
}
