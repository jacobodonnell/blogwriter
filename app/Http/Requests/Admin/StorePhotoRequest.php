<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePhotoRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('photos', 'slug')],
            'image_file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:'.config('app.max_image_upload_kb')],
            'caption' => ['nullable', 'string', 'max:5000'],
            'alt_text' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:private,public'],
            'taken_at' => ['nullable', 'date'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];
    }
}
