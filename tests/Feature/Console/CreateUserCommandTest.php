<?php

declare(strict_types=1);

use App\Models\User;

it('creates a user when none exists', function (): void {
    $this->artisan('blogwriter:create-user', [
        '--name' => 'Test User',
        '--email' => 'test@example.com',
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

    $this->artisan('blogwriter:create-user', [
        '--name' => 'New User',
        '--email' => 'new@example.com',
        '--password' => 'SecurePass123!@#456',
        '--force' => true,
    ])->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    $this->assertDatabaseMissing('users', ['email' => 'old@example.com']);
});

it('prompts for confirmation when replacing without --force', function (): void {
    User::factory()->create(['name' => 'Existing']);

    $this->artisan('blogwriter:create-user', [
        '--name' => 'New User',
        '--email' => 'new@example.com',
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

    $this->artisan('blogwriter:create-user', [
        '--name' => 'Replacement',
        '--email' => 'replacement@example.com',
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsConfirmation('Replace this user?', 'yes')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', ['name' => 'Replacement']);
});

it('generates password when not provided', function (): void {
    $this->artisan('blogwriter:create-user', [
        '--name' => 'Test User',
        '--email' => 'test@example.com',
    ])
        ->expectsOutputToContain('Generated Password:')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
});

it('prompts interactively when name and email are not provided', function (): void {
    $this->artisan('blogwriter:create-user', [
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsQuestion("What is the user's display name?", 'Prompted User')
        ->expectsQuestion("What is the user's email address?", 'prompted@example.com')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', [
        'name' => 'Prompted User',
        'email' => 'prompted@example.com',
    ]);
});

it('prompts for email when only name is provided', function (): void {
    $this->artisan('blogwriter:create-user', [
        '--name' => 'Partial User',
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsQuestion("What is the user's email address?", 'partial@example.com')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
    $this->assertDatabaseHas('users', [
        'name' => 'Partial User',
        'email' => 'partial@example.com',
    ]);
});

it('fails with invalid email via --email option', function (): void {
    $this->artisan('blogwriter:create-user', [
        '--name' => 'Test User',
        '--email' => 'not-an-email',
        '--password' => 'SecurePass123!@#456',
    ])
        ->expectsOutputToContain('Invalid email')
        ->assertFailed();

    expect(User::count())->toBe(0);
});
