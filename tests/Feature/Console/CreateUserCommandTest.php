<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a user when none exists', function (): void {
    $this->artisan('blogwriter:user:create', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        '--password' => 'SecurePass123!@#456',
    ])->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('replaces existing user with --force', function (): void {
    User::factory()->create([
        'name' => 'Old User',
        'email' => 'old@example.com',
    ]);

    $this->artisan('blogwriter:user:create', [
        'name' => 'New User',
        'email' => 'new@example.com',
        '--password' => 'SecurePass123!@#456',
        '--force' => true,
    ])->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    $this->assertDatabaseMissing('users', ['email' => 'old@example.com']);
});

it('prompts for confirmation when replacing without --force', function (): void {
    User::factory()->create(['name' => 'Existing']);

    $this->artisan('blogwriter:user:create', [
        'name' => 'New User',
        'email' => 'new@example.com',
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsConfirmation('Replace this user?', 'no')
        ->assertSuccessful();

    // Original user should still exist
    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', ['name' => 'Existing']);
});

it('replaces user when confirmation is accepted', function (): void {
    User::factory()->create(['name' => 'Existing']);

    $this->artisan('blogwriter:user:create', [
        'name' => 'Replacement',
        'email' => 'replacement@example.com',
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsConfirmation('Replace this user?', 'yes')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', ['name' => 'Replacement']);
});

it('generates password when not provided', function (): void {
    $this->artisan('blogwriter:user:create', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ])
        ->expectsOutputToContain('Generated Password:')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
});
