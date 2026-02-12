<?php

namespace App\Actions;

use Illuminate\Support\Str;

final readonly class GenerateUniqueSlugAction
{
    public function handle(string $baseSlug, string $modelClass, ?int $ignoreId = null): string
    {
        $slug = Str::slug($baseSlug);
        $originalSlug = $slug;
        $count = 1;

        while ($modelClass::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $originalSlug.'-'.$count++;
        }

        return $slug;
    }
}
