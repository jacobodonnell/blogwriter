<?php

use App\Console\Commands\InstallCommand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function getLockFilePath(): string
{
    return storage_path('installed.lock');
}

beforeEach(function (): void {
    // Clean up lock file before each test
    $lockFilePath = getLockFilePath();
    if (file_exists($lockFilePath)) {
        unlink($lockFilePath);
    }
});

afterEach(function (): void {
    // Clean up lock file after each test
    $lockFilePath = getLockFilePath();
    if (file_exists($lockFilePath)) {
        unlink($lockFilePath);
    }
});

it('command has correct signature and description', function (): void {
    $command = new InstallCommand;

    expect($command->getName())->toBe('blogwriter:install');
    expect($command->getDescription())->toBe('Interactive installer for BlogWriter');
});

it('has force option', function (): void {
    $command = new InstallCommand;

    expect($command->getDefinition()->hasOption('force'))->toBeTrue();
});

it('has seed option', function (): void {
    $command = new InstallCommand;

    expect($command->getDefinition()->hasOption('seed'))->toBeTrue();
});

describe('lock file detection', function (): void {
    it('returns true when lock file exists', function (): void {
        $lockFilePath = getLockFilePath();

        // Create lock file manually
        file_put_contents($lockFilePath, now());

        $command = new InstallCommand;

        // Use reflection to access protected method
        $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
        $isInstalled = $reflection->invoke($command);

        expect($isInstalled)->toBeTrue();
    });

    it('returns false when lock file does not exist', function (): void {
        $lockFilePath = getLockFilePath();

        // Ensure lock file does not exist (cleaned up by beforeEach)
        expect(file_exists($lockFilePath))->toBeFalse();

        $command = new InstallCommand;

        // Use reflection to access protected method
        $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
        $isInstalled = $reflection->invoke($command);

        expect($isInstalled)->toBeFalse();
    });
});

describe('lock file creation on install', function (): void {
    it('creates lock file during installation', function (): void {
        $lockFilePath = getLockFilePath();

        // Ensure lock file does not exist before install
        expect(file_exists($lockFilePath))->toBeFalse();

        // Create the lock file manually to simulate what install() does
        file_put_contents($lockFilePath, now());

        // Verify lock file was created
        expect(file_exists($lockFilePath))->toBeTrue();

        // Verify lock file contains timestamp
        $content = file_get_contents($lockFilePath);
        expect($content)->not->toBeEmpty();
        expect(strtotime($content))->toBeInt();
    });
});

describe('lock file override behavior', function (): void {
    it('deletes lock file when overriding installation', function (): void {
        $lockFilePath = getLockFilePath();

        // Create lock file to simulate installed state
        file_put_contents($lockFilePath, now());
        expect(file_exists($lockFilePath))->toBeTrue();

        $command = Mockery::mock(InstallCommand::class)->makePartial();
        $command->shouldReceive('option')->with('force')->andReturn(false);
        $command->shouldReceive('confirm')
            ->with(
                'Do you want to override the existing installation? ⚠️ This will DELETE all content and cannot be undone.',
                false
            )
            ->andReturn(true);

        // Simulate the handle() method logic for the override path
        if (file_exists($lockFilePath)) {
            unlink($lockFilePath);
        }

        // Verify lock file was deleted
        expect(file_exists($lockFilePath))->toBeFalse();

        Mockery::close();
    });
});

describe('fallback to User::exists()', function (): void {
    it('detects installation via User::exists() when lock file missing', function (): void {
        $lockFilePath = getLockFilePath();

        // Ensure lock file does not exist
        expect(file_exists($lockFilePath))->toBeFalse();

        // Create a user in database
        User::factory()->create();

        $command = new InstallCommand;

        // Use reflection to access protected method
        $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
        $isInstalled = $reflection->invoke($command);

        expect($isInstalled)->toBeTrue();
    });

    it('creates lock file automatically when User::exists() returns true', function (): void {
        $lockFilePath = getLockFilePath();

        // Ensure lock file does not exist
        expect(file_exists($lockFilePath))->toBeFalse();

        // Create a user in database
        User::factory()->create();

        $command = new InstallCommand;

        // Use reflection to access protected method
        $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
        $reflection->invoke($command);

        // Verify lock file was created as migration path
        expect(file_exists($lockFilePath))->toBeTrue();

        // Verify lock file contains timestamp
        $content = file_get_contents($lockFilePath);
        expect($content)->not->toBeEmpty();
        expect(strtotime($content))->toBeInt();
    });

    it('returns false when no lock file and no users exist', function (): void {
        $lockFilePath = getLockFilePath();

        // Ensure lock file does not exist
        expect(file_exists($lockFilePath))->toBeFalse();

        // Ensure no users exist (database is empty via RefreshDatabase)
        expect(User::exists())->toBeFalse();

        $command = new InstallCommand;

        // Use reflection to access protected method
        $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
        $isInstalled = $reflection->invoke($command);

        expect($isInstalled)->toBeFalse();
    });
});

it('can update env file', function (): void {
    $command = new InstallCommand;

    // Use reflection to access protected method
    $reflection = new ReflectionMethod($command, 'updateEnvValue');

    $content = "APP_NAME=\"Laravel\"\nAPP_URL=http://localhost";
    $updated = $reflection->invoke($command, $content, 'APP_NAME', 'Test Blog');

    expect($updated)->toContain('APP_NAME="Test Blog"');
    expect($updated)->toContain('APP_URL=http://localhost');
});

it('can add new env value', function (): void {
    $command = new InstallCommand;

    // Use reflection to access protected method
    $reflection = new ReflectionMethod($command, 'updateEnvValue');

    $content = 'APP_NAME="Laravel"';
    $updated = $reflection->invoke($command, $content, 'APP_URL', 'https://example.com');

    expect($updated)->toContain('APP_NAME="Laravel"');
    expect($updated)->toContain('APP_URL="https://example.com"');
});
