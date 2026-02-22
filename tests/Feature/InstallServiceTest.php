<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\InstallService;

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

it('detects installed state from lock file with healthy database', function (): void {
    User::factory()->create();
    file_put_contents(storage_path('installed.lock'), now());

    expect($this->service->isAlreadyInstalled())->toBeTrue();
});

it('returns false and removes stale lock when database file is empty', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    // Point config at a temp empty file to simulate empty database
    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    file_put_contents($tempDb, '');
    config(['database.connections.sqlite.database' => $tempDb]);

    expect($this->service->isAlreadyInstalled())->toBeFalse()
        ->and(file_exists(storage_path('installed.lock')))->toBeFalse();

    @unlink($tempDb);
});

it('returns false and removes stale lock when database file is missing', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    @unlink($tempDb); // Ensure it doesn't exist
    config(['database.connections.sqlite.database' => $tempDb]);

    expect($this->service->isAlreadyInstalled())->toBeFalse()
        ->and(file_exists(storage_path('installed.lock')))->toBeFalse();
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
    $passphrase = App\Services\PasswordGenerator::generate();

    expect($passphrase)->toBeString()->not->toBeEmpty();
});

it('ensureDatabaseFile cleans orphaned WAL files and creates database', function (): void {
    // Use a temp path to avoid interfering with the in-memory test DB
    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    @unlink($tempDb); // Ensure it doesn't exist
    config(['database.connections.sqlite.database' => $tempDb]);

    // Create orphaned WAL files
    file_put_contents($tempDb.'-wal', 'orphaned');
    file_put_contents($tempDb.'-shm', 'orphaned');

    $this->service->ensureDatabaseFile();

    expect(file_exists($tempDb))->toBeTrue()
        ->and(file_exists($tempDb.'-wal'))->toBeFalse()
        ->and(file_exists($tempDb.'-shm'))->toBeFalse();

    @unlink($tempDb);
});

it('ensureDatabaseFile is a no-op when database exists', function (): void {
    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    file_put_contents($tempDb, 'existing');
    config(['database.connections.sqlite.database' => $tempDb]);

    $this->service->ensureDatabaseFile();

    expect(file_get_contents($tempDb))->toBe('existing');

    @unlink($tempDb);
});

it('ensureDatabaseFile is a no-op for memory databases', function (): void {
    config(['database.connections.sqlite.database' => ':memory:']);

    // Should not throw
    $this->service->ensureDatabaseFile();

    expect(true)->toBeTrue();
});

it('creates lock file', function (): void {
    $lockPath = storage_path('installed.lock');
    @unlink($lockPath);

    $this->service->createLockFile();

    expect(file_exists($lockPath))->toBeTrue();
});
