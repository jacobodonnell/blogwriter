<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Category;

final class CreateCategoryFromArticleAction
{
    public function handle(array &$data): void
    {
        if (! filled($data['new_category_name'] ?? null)) {
            return;
        }

        $category = Category::create([
            'name' => $data['new_category_name'],
            'slug' => filled($data['new_category_slug'] ?? null) ? $data['new_category_slug'] : null,
            'parent_id' => $data['new_category_parent_id'] ?? null,
            'description' => filled($data['new_category_description'] ?? null) ? $data['new_category_description'] : null,
        ]);

        $data['category_id'] = $category->id;
    }
}
