<?php

declare(strict_types=1);

namespace App\Support;

final class DemoSchedule
{
    private const int MAX_INTERVAL = 1440;

    public static function cronExpression(): string
    {
        $interval = self::effectiveInterval();
        $startHour = self::startHour();

        if ($interval < 60) {
            return sprintf('*/%d * * * *', $interval);
        }

        $hours = max(1, (int) ceil($interval / 60));

        if ($hours >= 24) {
            return sprintf('0 %d * * *', $startHour);
        }

        $hourList = [];
        for ($h = $startHour; count($hourList) < (int) (24 / $hours); $h = ($h + $hours) % 24) {
            $hourList[] = $h;
        }

        sort($hourList);

        return '0 '.implode(',', $hourList).' * * *';
    }

    public static function effectiveInterval(): int
    {
        return min(self::MAX_INTERVAL, max(1, self::configuredInterval()));
    }

    public static function startHour(): int
    {
        $hour = (int) (config('demo.reset_start_hour') ?? 0);

        return max(0, min(23, $hour));
    }

    public static function wasIntervalClamped(): bool
    {
        return self::configuredInterval() > self::MAX_INTERVAL;
    }

    private static function configuredInterval(): int
    {
        return (int) (config('demo.reset_interval') ?? 120);
    }
}
