<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
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
            'image_file' => 'required|file|image|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'caption' => 'nullable|string|max:5000',
            'alt_text' => 'required|string|max:500',
            'status' => 'required|in:draft,published',
            'taken_at' => 'nullable|date',
        ];
    }
}
