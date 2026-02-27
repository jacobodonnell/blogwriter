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

it('invalidates existing sessions after password reset', function (): void {
    $password = 'OriginalP@ss1234';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);

    // Log in via the form to establish a real session with password hash
    $this->post('/login', [
        'email' => $user->email,
        'password' => $password,
    ])->assertRedirect();

    // Verify the session is authenticated and has the password hash stored
    $this->get(route('admin.dashboard'))->assertOk();
    $this->assertAuthenticated();

    // Reset the password via CLI (changes the hash in the DB)
    $this->artisan('blogwriter:reset-password', [
        '--password' => 'NewSecureP@ss1234',
    ])->assertSuccessful();

    // Flush the cached auth guard user so the next request re-resolves from session
    auth()->forgetGuards();

    // The stored session hash no longer matches the DB hash,
    // so auth.session middleware should log the user out
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
