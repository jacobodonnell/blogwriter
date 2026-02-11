<?php

use App\Console\Commands\InstallCommand;
use App\Models\User;

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

it('detects existing installation', function (): void {
    User::factory()->create();

    $command = new InstallCommand;

    // Use reflection to access protected method
    $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
    $isInstalled = $reflection->invoke($command);

    expect($isInstalled)->toBeTrue();
});

it('detects no installation when users table empty', function (): void {
    $command = new InstallCommand;

    // Use reflection to access protected method
    $reflection = new ReflectionMethod($command, 'isAlreadyInstalled');
    $isInstalled = $reflection->invoke($command);

    expect($isInstalled)->toBeFalse();
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
