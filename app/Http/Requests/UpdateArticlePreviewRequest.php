<?php

namespace App\Http\Requests;

use App\Rules\PublishedPhoto;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticlePreviewRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'meta' => ['nullable', 'array'],
            'meta.meta_title' => ['nullable', 'string', 'max:255'],
            'meta.meta_description' => ['nullable', 'string', 'max:500'],
            'meta.og_image' => ['nullable', 'string', 'max:500'],
            'photo_id' => ['nullable', 'integer', 'exists:photos,id', new PublishedPhoto],
            'featured_image' => ['nullable', 'url', 'max:500'],
        ];
    }
}
