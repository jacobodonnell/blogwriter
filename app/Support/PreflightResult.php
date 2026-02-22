<?php

declare(strict_types=1);

namespace App\Support;

final readonly class PreflightResult
{
    /**
     * @param  array<int, string>  $missingCategorySlugs
     */
    public function __construct(
        public bool $ok,
        public array $missingCategorySlugs,
    ) {}
}
