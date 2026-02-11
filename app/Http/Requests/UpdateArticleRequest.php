<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
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
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,hidden'],
            'published_at' => ['nullable', 'date', 'required_if:status,published'],
            'featured_image' => ['nullable', 'string', 'max:500'],
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
            'published_at.required_if' => 'Published date is required when status is published.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $status = $this->input('status');
            $publishedAt = $this->input('published_at');

            if ($status === 'published' && is_null($publishedAt)) {
                $this->merge(['published_at' => now()]);
            }
        });
    }
}
