<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case Private = 'private';
    case Public = 'public';

    /**
     * Returns true only for Public status.
     */
    public function isPublic(): bool
    {
        return $this === self::Public;
    }

    /**
     * Returns true for Private status.
     */
    public function isPrivate(): bool
    {
        return $this === self::Private;
    }

    /**
     * Returns display name for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Private => 'Private',
            self::Public => 'Public',
        };
    }
}
