<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoH1Heading implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (preg_match('/^# (?!#)/m', (string) $value)) {
            $fail('Content must not contain H1 headings (#). Use ## (H2) or smaller — the article title is already H1.');
        }
    }
}
