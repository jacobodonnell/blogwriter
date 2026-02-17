<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

trait ValidatesFeaturedImage
{
    public function validateFeaturedImage(Validator $validator): void
    {
        // Ensure only ONE method is used for featured image
        $methods = collect(['photo_id', 'featured_image', 'featured_image_file'])
            ->filter(fn ($field) => $field === 'featured_image_file' ? $this->hasFile($field) : $this->filled($field))
            ->count();

        if ($methods > 1) {
            $validator->errors()->add('featured_image', 'Please use only one method to add a featured image.');
        }

        // Validate URL file extension if present
        // Modern CDN URLs (Imgur, Unsplash, Cloudflare Images) don't have file extensions
        // So we only validate the extension if one is present in the URL path
        $featuredImage = $this->input('featured_image');
        if ($this->filled('featured_image') && Str::isUrl($featuredImage)) {
            $path = parse_url($featuredImage, PHP_URL_PATH) ?? '';
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            // If an extension is present, validate it's an image format
            // If no extension, allow it (CDN URLs without extensions)
            if ($extension && ! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $validator->errors()->add('featured_image', 'The URL must point to an image file (jpg, jpeg, png, gif, webp, svg), not a .'.$extension.' file.');
            }
        }

        // Validate uploaded image file is not empty
        if ($this->hasFile('featured_image_file')) {
            $file = $this->file('featured_image_file');

            // Check file size is not zero
            if ($file->getSize() === 0) {
                $validator->errors()->add('featured_image_file', 'The uploaded image file is empty.');
            }
        }
    }
}
