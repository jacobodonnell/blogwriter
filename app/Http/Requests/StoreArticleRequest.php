<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ArticleRules;
use App\Http\Requests\Concerns\ValidatesFeaturedImage;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    use ArticleRules;
    use ValidatesFeaturedImage;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge($this->sharedRules(), [
            'title' => ['nullable', 'string', 'min:3', 'max:255'],
            'slug' => ['nullable', 'string', 'unique:articles', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'status' => ['nullable', 'in:draft,published'],
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->sharedMessages();
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
