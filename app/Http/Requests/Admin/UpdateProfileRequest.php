<?php

namespace App\Http\Requests\Admin;

use App\Rules\NoH1Heading;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'profile_name' => 'required|string|max:255',
            'profile_bio' => ['nullable', 'string', 'max:1000', new NoH1Heading],
            'profile_avatar' => 'nullable|url|max:2048',
            'profile_github' => 'nullable|url|max:2048',
            'profile_mastodon' => 'nullable|url|max:2048',
            'profile_bluesky' => 'nullable|url|max:2048',
            'profile_email' => 'nullable|email|max:255',
        ];
    }
}
