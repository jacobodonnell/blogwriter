<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('resets password non-interactively with --password', function (): void {
    $user = User::factory()->create();

    $this->artisan('blogwriter:reset-password', [
        '--password' => 'NewSecureP@ss1234',
    ])->assertSuccessful();

    expect(Hash::check('NewSecureP@ss1234', $user->fresh()->password))->toBeTrue();
});

it('fails when no user exists', function (): void {
    $this->artisan('blogwriter:reset-password', [
        '--password' => 'NewSecureP@ss1234',
    ])->assertFailed();
});

it('validates password rules', function (): void {
    User::factory()->create();

    $this->artisan('blogwriter:reset-password', [
        '--password' => 'short',
    ])->assertFailed();
});
