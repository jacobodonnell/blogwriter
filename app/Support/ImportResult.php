<?php

declare(strict_types=1);

namespace App\Support;

final readonly class ImportResult
{
    /**
     * @param  array<string, string>  $errors  Keyed by slug
     */
    public function __construct(
        public int $imported,
        public int $skipped,
        public array $errors,
    ) {}
}
