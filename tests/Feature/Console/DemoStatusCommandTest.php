<?php

declare(strict_types=1);

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

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

    touch(storage_path('installed.lock'));

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutputToContain('45 minutes')
        ->expectsOutputToContain('Demo mode is active.');
});

it('warns when no lock file exists', function (): void {
    config()->set('demo.enabled', true);

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutput('No reset has been run yet. Run: php artisan demo:reset');
});

it('shows overdue when reset interval has passed', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 1);

    touch(storage_path('installed.lock'), time() - 120);

    $this->artisan('demo:status')
        ->assertSuccessful()
        ->expectsOutput('Next reset is overdue (scheduler may not be running).');
});
