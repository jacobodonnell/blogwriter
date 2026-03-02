<?php

declare(strict_types=1);

it('renders countdown component when demo is enabled', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 30);
    config()->set('demo.credentials.email', 'demo@test.com');
    config()->set('demo.credentials.password', 'secret');

    touch(storage_path('installed.lock'));

    $view = $this->blade('<x-demo-banner />');

    $view->assertSee('x-data="demoCountdown(', escape: false)
        ->assertSee('demo@test.com')
        ->assertSee('Demo mode');

    @unlink(storage_path('installed.lock'));
});

it('does not render when demo is disabled', function (): void {
    config()->set('demo.enabled', false);

    $view = $this->blade('<x-demo-banner />');

    $view->assertDontSee('Demo mode');
});

it('passes correct next reset timestamp', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.reset_interval', 15);

    $lockTime = time() - 300;
    touch(storage_path('installed.lock'), $lockTime);

    $expectedNextReset = $lockTime + (15 * 60);

    $view = $this->blade('<x-demo-banner />');

    $view->assertSee("demoCountdown({$expectedNextReset})", escape: false);

    @unlink(storage_path('installed.lock'));
});
