<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePageSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page_home_subtitle' => 'nullable|string|max:500',
            'page_articles_subtitle' => 'nullable|string|max:500',
            'page_photos_subtitle' => 'nullable|string|max:500',
        ];
    }
}
