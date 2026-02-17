<?php

use App\Services\ResetService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

it('cancels when confirmation is declined', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $this->artisan('blogwriter:uninstall')
        ->expectsConfirmation('This will DELETE all content, users, and uploaded files. Are you sure?', 'no')
        ->expectsOutput('Uninstall cancelled.')
        ->assertSuccessful();

    expect(file_exists(storage_path('installed.lock')))->toBeTrue();
});

it('uninstalls with --force flag', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $mock = Mockery::mock(ResetService::class);
    $mock->shouldReceive('reset')->once()->andReturnUsing(function (): int {
        @unlink(storage_path('installed.lock'));

        return Command::SUCCESS;
    });
    $this->app->instance(ResetService::class, $mock);

    $this->artisan('blogwriter:uninstall --force')
        ->assertSuccessful();

    expect(file_exists(storage_path('installed.lock')))->toBeFalse();
});

it('handles missing installation gracefully', function (): void {
    @unlink(storage_path('installed.lock'));

    $mock = Mockery::mock(ResetService::class);
    $mock->shouldReceive('reset')->once()->andReturn(Command::SUCCESS);
    $this->app->instance(ResetService::class, $mock);

    $this->artisan('blogwriter:uninstall --force')
        ->assertSuccessful();
});
