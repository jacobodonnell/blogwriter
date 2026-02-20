<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Validation\Rules\Password;

final class PasswordRules
{
    public static function rules(): Password
    {
        if (config('auth.bypass_password_rules')) {
            return Password::min(8);
        }

        return Password::min(16)
            ->letters()
            ->numbers()
            ->symbols();
    }

    public static function messages(): array
    {
        return [
            'password.min' => 'Password must be at least 16 characters.',
            'password.letters' => 'Password must contain at least one letter.',
            'password.numbers' => 'Password must contain at least one number.',
            'password.symbols' => 'Password must contain at least one symbol.',
        ];
    }
}
