<?php

use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    // Ensure prohibition is in a known state before each test
    DB::prohibitDestructiveCommands(false);
});

afterEach(function (): void {
    // Always restore prohibition after tests
    DB::prohibitDestructiveCommands(true);
});

it('restores destructive command prohibition to original state', function (): void {
    // Start with prohibition enabled (production-like state)
    DB::prohibitDestructiveCommands(true);

    // Check initial state using reflection (like InstallCommand does)
    $reflection = new ReflectionClass(FreshCommand::class);
    $property = $reflection->getProperty('prohibitedFromRunning');

    // Verify it starts prohibited
    $originalState = $property->getValue();
    expect($originalState)->toBeTrue();

    // Simulate what InstallCommand::handle() does:
    // 1. Store original state
    $wasProhibited = $property->getValue();

    // 2. Disable prohibition
    DB::prohibitDestructiveCommands(false);
    expect($property->getValue())->toBeFalse();

    // 3. Do work (installer would run here)
    // ... installation happens ...

    // 4. Restore in finally block
    DB::prohibitDestructiveCommands($wasProhibited);

    // Verify it's back to original state
    expect($property->getValue())->toBeTrue();
});

it('restores prohibition even when starting from disabled state', function (): void {
    // Start with prohibition disabled
    DB::prohibitDestructiveCommands(false);

    $reflection = new ReflectionClass(FreshCommand::class);
    $property = $reflection->getProperty('prohibitedFromRunning');

    // Store and verify initial state is false
    $wasProhibited = $property->getValue();
    expect($wasProhibited)->toBeFalse();

    // Disable again (redundant but matches installer flow)
    DB::prohibitDestructiveCommands(false);

    // Restore
    DB::prohibitDestructiveCommands($wasProhibited);

    // Should still be false
    expect($property->getValue())->toBeFalse();
});

it('DB::prohibitDestructiveCommands affects all destructive commands', function (): void {
    // Enable prohibition
    DB::prohibitDestructiveCommands(true);

    // Check all relevant commands are affected
    $commands = [
        FreshCommand::class,
        WipeCommand::class,
    ];

    foreach ($commands as $commandClass) {
        $reflection = new ReflectionClass($commandClass);
        $property = $reflection->getProperty('prohibitedFromRunning');
        expect($property->getValue())->toBeTrue(
            "{$commandClass} should be prohibited"
        );
    }

    // Disable
    DB::prohibitDestructiveCommands(false);

    foreach ($commands as $commandClass) {
        $reflection = new ReflectionClass($commandClass);
        $property = $reflection->getProperty('prohibitedFromRunning');
        expect($property->getValue())->toBeFalse(
            "{$commandClass} should not be prohibited"
        );
    }
});

it('reflection can access private static properties', function (): void {
    // This test verifies the technique used in InstallCommand works
    $reflection = new ReflectionClass(FreshCommand::class);
    $property = $reflection->getProperty('prohibitedFromRunning');

    // Set a known value
    DB::prohibitDestructiveCommands(true);
    expect($property->getValue())->toBeTrue();

    // Change it
    DB::prohibitDestructiveCommands(false);
    expect($property->getValue())->toBeFalse();

    // The reflection correctly tracks the static property
});
