<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

trait ChecksExternalUrls
{
    private function isExternalUrl(?string $url): bool
    {
        return $url !== null && str_starts_with($url, 'http');
    }
}
