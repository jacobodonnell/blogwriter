<?php

namespace App\Http\Requests;

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
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => ['required', 'string', 'unique:articles', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,hidden'],
            'featured_image' => ['nullable', 'string', 'url', 'max:500'],
            'featured_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
                // Check for both URL and file upload
                if ($this->has('featured_image') && $this->filled('featured_image') && $this->hasFile('featured_image_file')) {
                    $validator->errors()->add('featured_image', 'Cannot provide both URL and file upload.');
                    $validator->errors()->add('featured_image_file', 'Cannot provide both URL and file upload.');
                }

                // Validate URL points to an image file
                $featuredImage = $this->input('featured_image');
                if ($this->filled('featured_image') && \Illuminate\Support\Str::isUrl($featuredImage)) {
                    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                    $extension = strtolower(pathinfo(parse_url($featuredImage, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

                    if (! in_array($extension, $validExtensions)) {
                        $validator->errors()->add('featured_image', 'The URL must point to a valid image file (jpg, jpeg, png, gif, webp, svg).');
                    }
                }
            },
        ];
    }
}
