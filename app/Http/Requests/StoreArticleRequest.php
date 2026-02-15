<?php

namespace App\Http\Requests;

use App\Rules\NoH1Heading;
use App\Rules\PublishedPhoto;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'min:3', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:articles', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', new NoH1Heading],
            'status' => ['nullable', 'in:draft,published'],
            'photo_id' => ['nullable', 'exists:photos,id', new PublishedPhoto],
            'featured_image' => ['nullable', 'string', 'url', 'max:500'],
            'featured_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_caption' => ['nullable', 'string', 'max:500'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'meta' => ['nullable', 'array'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.og_image' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator): void {
                // Ensure only ONE method is used for featured image
                $methods = collect(['photo_id', 'featured_image', 'featured_image_file'])
                    ->filter(fn ($field) => $field === 'featured_image_file' ? $this->hasFile($field) : $this->filled($field))
                    ->count();

                if ($methods > 1) {
                    $validator->errors()->add('featured_image', 'Please use only one method to add a featured image.');
                }

                // Check for both URL and file upload (legacy check - now redundant with above)
                if ($this->has('featured_image') && $this->filled('featured_image') && $this->hasFile('featured_image_file')) {
                    $validator->errors()->add('featured_image', 'Cannot provide both URL and file upload.');
                    $validator->errors()->add('featured_image_file', 'Cannot provide both URL and file upload.');
                }

                // Validate URL file extension if present
                // Modern CDN URLs (Imgur, Unsplash, Cloudflare Images) don't have file extensions
                // So we only validate the extension if one is present in the URL path
                $featuredImage = $this->input('featured_image');
                if ($this->filled('featured_image') && \Illuminate\Support\Str::isUrl($featuredImage)) {
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
            },
        ];
    }
}
