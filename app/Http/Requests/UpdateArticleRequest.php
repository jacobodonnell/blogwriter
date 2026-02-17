<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesFeaturedImage;
use App\Rules\NoH1Heading;
use App\Rules\PublishedPhoto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    use ValidatesFeaturedImage;

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
        $articleId = $this->route('article')?->id ?? $this->route('id');

        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('articles', 'slug')->ignore($articleId),
            ],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', new NoH1Heading],
            'status' => ['required', 'in:draft,published'],
            'photo_id' => ['nullable', 'exists:photos,id', new PublishedPhoto],
            'featured_image' => ['nullable', 'string', 'url', 'max:500'],
            'featured_image_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'featured_image_alt' => ['nullable', 'string', 'max:255'],
            'featured_image_caption' => ['nullable', 'string', 'max:500'],
            'remove_featured_image' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'meta' => ['nullable', 'array'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.og_image' => ['nullable', 'string', 'max:500'],
            'meta.featured_image_caption' => ['nullable', 'string', 'max:500'],
            'meta.use_photo_caption' => ['nullable'],
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
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function after(): array
    {
        return [
            $this->validateFeaturedImage(...),
        ];
    }
}
