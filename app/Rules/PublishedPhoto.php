<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Photo;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class PublishedPhoto implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (! Photo::published()->find($value)) {
            $fail('The selected photo must be published and publicly accessible.');
        }
    }
}
