<?php

namespace App\Enums;

enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';

    /**
     * Returns true only for Published status.
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    /**
     * Returns true for Draft status.
     */
    public function isPrivate(): bool
    {
        return $this === self::Draft;
    }

    /**
     * Returns display name for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
        };
    }
}
