<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
    @unlink(storage_path('install_seed_allowed'));
});

it('shows install page when not installed', function (): void {
    $response = $this->get('/install');

    $response->assertSuccessful()
        ->assertSee('BlogWriter Installer');
});

it('redirects to admin when already installed', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $response = $this->get('/install');

    $response->assertRedirect('/admin');
});

it('returns requirements from check endpoint', function (): void {
    $response = $this->postJson('/install/check');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'requirements' => [
                '*' => ['name', 'passed', 'value'],
            ],
            'allPassed',
        ]);
});

it('returns 403 from check endpoint when already installed', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $response = $this->postJson('/install/check');

    $response->assertForbidden();
});

it('validates account fields', function (): void {
    $response = $this->postJson('/install/account', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password', 'site_name', 'site_url']);
});

it('validates email format on account endpoint', function (): void {
    $response = $this->postJson('/install/account', [
        'name' => 'Test',
        'email' => 'not-an-email',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'secure-passphrase-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('validates password confirmation on account endpoint', function (): void {
    $response = $this->postJson('/install/account', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'different-password-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('stores config in session on valid account submission', function (): void {
    $response = $this->postJson('/install/account', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'secure-passphrase-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
        'seed_demo' => false,
    ]);

    $response->assertSuccessful()
        ->assertJson(['success' => true]);
});

it('returns 403 from account endpoint when already installed', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $response = $this->postJson('/install/account', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'secure-passphrase-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
    ]);

    $response->assertForbidden();
});

it('finalizes installation creating user and lock file', function (): void {
    // First store config in session
    $this->postJson('/install/account', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'secure-passphrase-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
        'seed_demo' => false,
    ]);

    $response = $this->postJson('/install/finalize');

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'siteUrl' => 'https://example.com',
            'adminUrl' => 'https://example.com/admin',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
    expect(file_exists(storage_path('installed.lock')))->toBeTrue();
});

it('returns 422 when finalizing without account config', function (): void {
    $response = $this->postJson('/install/finalize');

    $response->assertStatus(422);
});

it('returns 403 from all post routes when already installed', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $this->postJson('/install/check')->assertForbidden();
    $this->postJson('/install/account')->assertForbidden();
    $this->postJson('/install/passphrase')->assertForbidden();
});

it('completes full install flow with demo seeding', function (): void {
    // Step 1: Submit account config with seed_demo enabled
    $this->postJson('/install/account', [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'secure-passphrase-here-42!',
        'password_confirmation' => 'secure-passphrase-here-42!',
        'site_name' => 'My Blog',
        'site_url' => 'https://example.com',
        'seed_demo' => true,
    ])->assertSuccessful();

    // Step 2: Finalize (clears caches, which invalidates session/CSRF)
    $this->postJson('/install/finalize')
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    // Step 3: Seed demo content — previously failed with CSRF mismatch
    $this->postJson('/install/seed')
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
});

it('returns a passphrase string from passphrase endpoint', function (): void {
    $response = $this->postJson('/install/passphrase');

    $response->assertSuccessful()
        ->assertJsonStructure(['passphrase']);

    expect($response->json('passphrase'))->toBeString()->not->toBeEmpty();
});
