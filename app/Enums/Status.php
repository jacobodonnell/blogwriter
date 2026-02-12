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

    /**
     * Returns Phosphor icon class for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'ph-pencil',
            self::Published => 'ph-check',
        };
    }

    /**
     * Returns DaisyUI color for the status.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Published => 'success',
        };
    }

    /**
     * Returns all public statuses.
     *
     * @return array<self>
     */
    public static function publicStatuses(): array
    {
        return [self::Published];
    }

    /**
     * Returns all private statuses.
     *
     * @return array<self>
     */
    public static function privateStatuses(): array
    {
        return [self::Draft];
    }

    /**
     * Returns options array for select inputs.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
        ];
    }
}
