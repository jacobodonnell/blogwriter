<?php

namespace App\Console\Commands\Concerns;

use App\Services\PasswordRules;
use Illuminate\Support\Facades\Validator;

trait ValidatesInput
{
    protected function validateUrl(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) ? null : 'Please enter a valid URL.';
    }

    protected function validateEmail(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Please enter a valid email address.';
    }

    protected function validatePassword(string $value): ?string
    {
        $validator = Validator::make(
            ['password' => $value],
            ['password' => ['required', 'string', PasswordRules::rules()]],
            PasswordRules::messages(),
        );

        if ($validator->fails()) {
            return $validator->errors()->first('password');
        }

        return null;
    }
}
