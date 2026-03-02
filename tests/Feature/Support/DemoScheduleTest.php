<?php

declare(strict_types=1);

use App\Support\DemoSchedule;

it('builds minute-level cron for intervals under 60', function (int $interval, string $expected): void {
    config()->set('demo.reset_interval', $interval);

    expect(DemoSchedule::cronExpression())->toBe($expected);
})->with([
    [15, '*/15 * * * *'],
    [30, '*/30 * * * *'],
    [45, '*/45 * * * *'],
    [1, '*/1 * * * *'],
    [59, '*/59 * * * *'],
]);

it('builds hour-level cron for intervals of 60 or more with default start hour', function (int $interval, string $expected): void {
    config()->set('demo.reset_interval', $interval);
    config()->set('demo.reset_start_hour', 0);

    expect(DemoSchedule::cronExpression())->toBe($expected);
})->with([
    [60, '0 0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23 * * *'],
    [120, '0 0,2,4,6,8,10,12,14,16,18,20,22 * * *'],
    [180, '0 0,3,6,9,12,15,18,21 * * *'],
    [240, '0 0,4,8,12,16,20 * * *'],
    [360, '0 0,6,12,18 * * *'],
    [720, '0 0,12 * * *'],
    [1440, '0 0 * * *'],
]);

it('applies start hour offset to hourly cron', function (int $interval, int $startHour, string $expected): void {
    config()->set('demo.reset_interval', $interval);
    config()->set('demo.reset_start_hour', $startHour);

    expect(DemoSchedule::cronExpression())->toBe($expected);
})->with([
    [1440, 21, '0 21 * * *'],
    [1440, 9, '0 9 * * *'],
    [720, 21, '0 9,21 * * *'],
    [720, 6, '0 6,18 * * *'],
    [240, 21, '0 1,5,9,13,17,21 * * *'],
    [360, 3, '0 3,9,15,21 * * *'],
    [120, 1, '0 1,3,5,7,9,11,13,15,17,19,21,23 * * *'],
]);

it('ignores start hour for sub-hourly intervals', function (): void {
    config()->set('demo.reset_interval', 30);
    config()->set('demo.reset_start_hour', 21);

    expect(DemoSchedule::cronExpression())->toBe('*/30 * * * *');
});

it('clamps intervals above 1440 to 24 hours', function (): void {
    config()->set('demo.reset_interval', 2880);
    config()->set('demo.reset_start_hour', 0);

    expect(DemoSchedule::effectiveInterval())->toBe(1440)
        ->and(DemoSchedule::cronExpression())->toBe('0 0 * * *');
});

it('clamps intervals below 1 to 1 minute', function (): void {
    config()->set('demo.reset_interval', 0);

    expect(DemoSchedule::effectiveInterval())->toBe(1);
});

it('reports when interval was clamped', function (): void {
    config()->set('demo.reset_interval', 2880);

    expect(DemoSchedule::wasIntervalClamped())->toBeTrue();
});

it('reports when interval was not clamped', function (): void {
    config()->set('demo.reset_interval', 120);

    expect(DemoSchedule::wasIntervalClamped())->toBeFalse();
});

it('uses default interval of 120 when config is missing', function (): void {
    config()->set('demo.reset_interval', null);
    config()->set('demo.reset_start_hour', 0);

    expect(DemoSchedule::effectiveInterval())->toBe(120)
        ->and(DemoSchedule::cronExpression())->toBe('0 0,2,4,6,8,10,12,14,16,18,20,22 * * *');
});

it('clamps start hour to valid range', function (int $input, int $expected): void {
    config()->set('demo.reset_start_hour', $input);

    expect(DemoSchedule::startHour())->toBe($expected);
})->with([
    [0, 0],
    [23, 23],
    [-1, 0],
    [24, 23],
    [100, 23],
]);

it('defaults start hour to 0 when config is missing', function (): void {
    config()->set('demo.reset_start_hour', null);

    expect(DemoSchedule::startHour())->toBe(0);
});
