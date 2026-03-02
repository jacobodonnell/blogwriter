<?php

declare(strict_types=1);

namespace App\Support;

final class DemoSchedule
{
    private const int MAX_INTERVAL = 1440;

    public static function cronExpression(): string
    {
        $interval = self::effectiveInterval();

        if ($interval < 60) {
            return "*/{$interval} * * * *";
        }

        $hours = max(1, (int) ceil($interval / 60));

        return "0 */{$hours} * * *";
    }

    public static function effectiveInterval(): int
    {
        return min(self::MAX_INTERVAL, max(1, (int) self::configuredInterval()));
    }

    public static function wasIntervalClamped(): bool
    {
        return (int) self::configuredInterval() > self::MAX_INTERVAL;
    }

    private static function configuredInterval(): int
    {
        return (int) (config('demo.reset_interval') ?? 120);
    }
}
