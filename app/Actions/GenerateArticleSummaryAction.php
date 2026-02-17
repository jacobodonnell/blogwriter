<?php

namespace App\Actions;

use App\Support\Markdown;
use Illuminate\Support\Str;

final readonly class GenerateArticleSummaryAction
{
    public function handle(?string $summary, ?string $content): string
    {
        if (blank($summary)) {
            return Str::limit(Markdown::toPlainText($content ?? ''), 255);
        }

        return $summary;
    }
}
