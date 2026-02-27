<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ArticleRules;
use App\Http\Requests\Concerns\ValidatesFeaturedImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateArticleRequest extends FormRequest
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
        $articleId = $this->route('article')?->id ?? $this->route('id');

        return array_merge($this->sharedRules(), [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('articles', 'slug')->ignore($articleId),
            ],
            'status' => ['required', 'in:private,public'],
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
