<?php

declare(strict_types=1);

use App\Models\User;

it('passes the controller check when SiteSettingsController exists', function (): void {
    expect(file_exists(app_path('Http/Controllers/Admin/SiteSettingsController.php')))->toBeTrue();

    $this->artisan('blogwriter:diagnose')
        ->expectsOutputToContain('✓ Controllers exist')
        ->assertSuccessful();
});

it('passes the user check when a user exists', function (): void {
    User::factory()->create();

    $this->artisan('blogwriter:diagnose')
        ->expectsOutputToContain('✓ Database has users')
        ->assertSuccessful();
});

it('fails the user check when no users exist', function (): void {
    User::query()->delete();

    $this->artisan('blogwriter:diagnose')
        ->expectsOutputToContain('✗ Database has users')
        ->assertSuccessful();
});
