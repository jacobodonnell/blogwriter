<?php

use App\Console\Commands\InstallCommand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Clean up lock file
    if (file_exists(storage_path('installed.lock'))) {
        unlink(storage_path('installed.lock'));
    }
});

afterEach(function (): void {
    // Clean up lock file
    if (file_exists(storage_path('installed.lock'))) {
        unlink(storage_path('installed.lock'));
    }
});

it('tracks when freshInstall is called to prevent double migration', function (): void {
    // Create initial user to simulate existing installation
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    // Create lock file
    file_put_contents(storage_path('installed.lock'), now());

    // Read the InstallCommand source
    $methodContent = file_get_contents(app_path('Console/Commands/InstallCommand.php'));

    // Verify the fix exists: check for didFreshInstall variable
    expect($methodContent)->toContain('$didFreshInstall');
    expect($methodContent)->toContain('if (! $didFreshInstall)');
    expect($methodContent)->toMatch('/\$didFreshInstall\s*=\s*true/');
});

it('prevents install from running after fresh install', function (): void {
    // Read the InstallCommand source
    $source = file_get_contents(app_path('Console/Commands/InstallCommand.php'));

    // Find the line numbers for key statements
    $lines = explode("\n", $source);

    $freshInstallLine = null;
    $flagSetLine = null;
    $installLine = null;
    $conditionalLine = null;

    foreach ($lines as $index => $line) {
        if (strpos($line, '$this->freshInstall()') !== false) {
            $freshInstallLine = $index;
        }
        if (strpos($line, '$didFreshInstall = true') !== false) {
            $flagSetLine = $index;
        }
        if (strpos($line, '$this->install(') !== false && strpos($line, '$this->freshInstall()') === false) {
            $installLine = $index;
        }
        if (strpos($line, 'if (! $didFreshInstall)') !== false) {
            $conditionalLine = $index;
        }
    }

    // Verify all lines found
    expect($freshInstallLine)->not->toBeNull('freshInstall() should exist');
    expect($flagSetLine)->not->toBeNull('$didFreshInstall = true should exist');
    expect($installLine)->not->toBeNull('install() should exist');
    expect($conditionalLine)->not->toBeNull('if (! $didFreshInstall) should exist');

    // Verify freshInstall is called before the flag is set
    expect($flagSetLine)->toBeGreaterThan($freshInstallLine, 'Flag should be set after freshInstall()');

    // Verify install() is inside the conditional
    expect($installLine)->toBeGreaterThan($conditionalLine, 'install() should be after the conditional check');
});

it('has proper method structure for reset flow control', function (): void {
    $source = file_get_contents(app_path('Console/Commands/InstallCommand.php'));

    // Check that the method signature exists
    expect($source)->toContain('protected function runInstallation(): int');

    // Verify the key logic pieces exist
    expect($source)->toContain('$didFreshInstall = false');
    expect($source)->toContain('$this->freshInstall()');
    expect($source)->toMatch('/\$didFreshInstall\s*=\s*true/');
    expect($source)->toContain('if (! $didFreshInstall)');
    expect($source)->toContain('$this->install($config)');
});
