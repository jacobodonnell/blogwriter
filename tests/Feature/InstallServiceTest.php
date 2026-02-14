<?php

use App\Models\User;
use App\Services\InstallService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = app(InstallService::class);
});

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

it('returns structured requirements array', function (): void {
    $result = $this->service->checkRequirements();

    expect($result)->toHaveKeys(['requirements', 'allPassed'])
        ->and($result['requirements'])->toBeArray()
        ->and($result['requirements'][0])->toHaveKeys(['name', 'passed', 'value']);
});

it('detects not installed when no lock file and no users', function (): void {
    expect($this->service->isAlreadyInstalled())->toBeFalse();
});

it('detects installed state from lock file', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    expect($this->service->isAlreadyInstalled())->toBeTrue();
});

it('detects installed state from existing user', function (): void {
    User::factory()->create();

    expect($this->service->isAlreadyInstalled())->toBeTrue();
});

it('creates a user with correct attributes', function (): void {
    $config = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'secure-password-123!',
    ];

    $user = $this->service->createUser($config);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->email_verified_at)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('replaces existing user when creating a new one', function (): void {
    User::factory()->create(['email' => 'old@example.com']);

    $this->service->createUser([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'secure-password-123!',
    ]);

    expect(User::count())->toBe(1);
    $this->assertDatabaseMissing('users', ['email' => 'old@example.com']);
    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
});

it('generates a passphrase string', function (): void {
    $passphrase = $this->service->generatePassphrase();

    expect($passphrase)->toBeString()->not->toBeEmpty();
});

it('creates lock file', function (): void {
    $lockPath = storage_path('installed.lock');
    @unlink($lockPath);

    $this->service->createLockFile();

    expect(file_exists($lockPath))->toBeTrue();
});
