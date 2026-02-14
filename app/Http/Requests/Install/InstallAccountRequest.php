<?php

namespace App\Http\Requests\Install;

use App\Services\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;

class InstallAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! file_exists(storage_path('installed.lock'));
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', PasswordRules::rules()],
            'site_name' => ['required', 'string', 'max:255'],
            'site_url' => ['required', 'url', 'max:255'],
            'seed_demo' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...PasswordRules::messages(),
            'site_url.url' => 'Please enter a valid URL (e.g. https://example.com).',
        ];
    }
}
