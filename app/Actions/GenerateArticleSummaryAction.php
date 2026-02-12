<?php

namespace App\Actions;

use Illuminate\Support\Str;

final readonly class GenerateArticleSummaryAction
{
    public function handle(?string $summary, string $content): string
    {
        if (in_array($summary, [null, '', '0'], true)) {
            return Str::limit(strip_tags($content), 255);
        }

        return $summary;
    }
}
