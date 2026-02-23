<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Rules\NoH1Heading;
use App\Rules\PublishedPhoto;

trait ArticleRules
{
    /**
     * Get the shared validation rules for articles.
     *
     * @return array<string, array<mixed>>
     */
    protected function sharedRules(): array
    {
        return [
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', new NoH1Heading],
            'photo_id' => ['nullable', 'integer', 'exists:photos,id', new PublishedPhoto],
            'featured_image' => ['nullable', 'string', 'url', 'max:500'],
            'featured_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_caption' => ['nullable', 'string', 'max:500'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'new_category_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'new_category_slug' => ['nullable', 'string', 'regex:/^[a-z0-9-]+$/', 'unique:categories,slug'],
            'new_category_parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'new_category_description' => ['nullable', 'string', 'max:500'],
            'meta' => ['nullable', 'array'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.og_image' => ['nullable', 'string', 'max:500'],
            'meta.featured_image_caption' => ['nullable', 'string', 'max:500'],
            'meta.use_photo_caption' => ['nullable'],
        ];
    }

    /**
     * Get the shared validation messages for articles.
     *
     * @return array<string, string>
     */
    protected function sharedMessages(): array
    {
        return [
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'category_id.exists' => 'The selected category does not exist.',
            'new_category_name.min' => 'Category name must be at least 2 characters.',
            'new_category_slug.regex' => 'Category slug can only contain lowercase letters, numbers, and hyphens.',
            'new_category_slug.unique' => 'A category with that slug already exists.',
        ];
    }
}
