<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateAppearanceRequest extends FormRequest
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
            'theme_light' => ['required', 'string', Rule::in(config('appearance.themes_light'))],
            'theme_dark' => ['required', 'string', Rule::in(config('appearance.themes_dark'))],
            'theme_font' => ['required', 'string', Rule::in(array_keys(config('appearance.fonts')))],
        ];
    }
}
