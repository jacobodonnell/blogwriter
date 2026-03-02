<?php

declare(strict_types=1);

it('warns when demo mode is disabled', function (): void {
    config()->set('demo.enabled', false);

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutput('Demo mode is not enabled.');
});

it('shows status with config interval', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 45);
    config()->set('demo.credentials.email', 'test@example.com');
    config()->set('demo.credentials.password', 'secret');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('45 minutes')
        ->expectsOutputToContain('Demo mode is active.');
});

it('shows next reset time from cron schedule', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 15);

    $cron = new Cron\CronExpression(App\Support\DemoSchedule::cronExpression());
    $expectedNext = $cron->getNextRunDate()->format('Y-m-d H:i:s');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain("Next reset at: {$expectedNext}");
});

it('shows effective interval for intervals over 60 minutes', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 120);
    config()->set('demo.credentials.email', 'test@example.com');
    config()->set('demo.credentials.password', 'secret');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('120 minutes');
});

it('shows start hour in status output', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 1440);
    config()->set('demo.reset_start_hour', 21);
    config()->set('demo.credentials.email', 'test@example.com');
    config()->set('demo.credentials.password', 'secret');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('21:00');
});

it('shows zero-padded start hour in status output', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 120);
    config()->set('demo.reset_start_hour', 3);
    config()->set('demo.credentials.email', 'test@example.com');
    config()->set('demo.credentials.password', 'secret');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('03:00');
});

it('warns when interval exceeds maximum', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 2880);
    config()->set('demo.credentials.email', 'test@example.com');
    config()->set('demo.credentials.password', 'secret');

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('DEMO_RESET_INTERVAL is set to 2880 minutes');
});
