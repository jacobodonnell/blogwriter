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

it('builds hour-level cron for intervals of 60 or more', function (int $interval, string $expected): void {
    config()->set('demo.reset_interval', $interval);

    expect(DemoSchedule::cronExpression())->toBe($expected);
})->with([
    [60, '0 */1 * * *'],
    [120, '0 */2 * * *'],
    [180, '0 */3 * * *'],
    [720, '0 */12 * * *'],
    [1440, '0 */24 * * *'],
]);

it('clamps intervals above 1440 to 24 hours', function (): void {
    config()->set('demo.reset_interval', 2880);

    expect(DemoSchedule::effectiveInterval())->toBe(1440)
        ->and(DemoSchedule::cronExpression())->toBe('0 */24 * * *');
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

    expect(DemoSchedule::effectiveInterval())->toBe(120)
        ->and(DemoSchedule::cronExpression())->toBe('0 */2 * * *');
});
