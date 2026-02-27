<?php

declare(strict_types=1);

use App\Contracts\Resettable;
use App\Models\User;
use Illuminate\Console\Command;

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

it('refuses to run when demo mode is disabled', function (): void {
    config()->set('demo.enabled', false);

    $this->artisan('demo:reset')
        ->assertFailed()
        ->expectsOutput('Demo mode is not enabled. Set DEMO_MODE=true in your .env file.');
});

it('resets and re-seeds when demo mode is enabled', function (): void {
    config()->set('demo.enabled', true);
    config()->set('demo.credentials.email', 'demo@blogwriter.dev');
    config()->set('demo.credentials.password', 'demo1234');

    $mock = Mockery::mock(Resettable::class);
    $mock->shouldReceive('reset')->once()->andReturn(Command::SUCCESS);
    $this->app->instance(Resettable::class, $mock);

    $this->artisan('demo:reset')
        ->assertSuccessful();

    expect(User::where('email', 'demo@blogwriter.dev')->exists())->toBeTrue()
        ->and(file_exists(storage_path('installed.lock')))->toBeTrue();
});

it('fails when reset service fails', function (): void {
    config()->set('demo.enabled', true);

    $mock = Mockery::mock(Resettable::class);
    $mock->shouldReceive('reset')->once()->andReturn(Command::FAILURE);
    $this->app->instance(Resettable::class, $mock);

    $this->artisan('demo:reset')
        ->assertFailed();
});
