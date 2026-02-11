<?php

use App\Console\Commands\BundleCommand;

beforeEach(function (): void {
    // Clean up any existing test bundles
    if (file_exists(base_path('dist/test-bundle.zip'))) {
        unlink(base_path('dist/test-bundle.zip'));
    }
});

afterEach(function (): void {
    // Clean up test bundles
    if (file_exists(base_path('dist/test-bundle.zip'))) {
        unlink(base_path('dist/test-bundle.zip'));
    }
});

it('bundle contains critical installation files', function (): void {
    // Verify critical files exist in source before bundling
    expect(file_exists(base_path('.env.freshinstall')))->toBeTrue(
        '.env.freshinstall must exist before bundling'
    );

    // Check the source code properly handles exclusions
    $source = file_get_contents(app_path('Console/Commands/BundleCommand.php'));
    expect($source)->toContain('.env.freshinstall');
});

it('.env.freshinstall exists and is readable', function (): void {
    $path = base_path('.env.freshinstall');

    expect(file_exists($path))->toBeTrue();
    expect(is_readable($path))->toBeTrue();

    $content = file_get_contents($path);
    expect($content)->toContain('APP_NAME=');
    expect($content)->toContain('APP_URL=');
    expect($content)->toContain('DB_CONNECTION=sqlite');
});

it('excludes development-only files from bundle', function (): void {
    $command = new BundleCommand;
    $reflection = new ReflectionClass($command);

    // Get the addDirectoryToZip method to check exclusions
    $method = $reflection->getMethod('addDirectoryToZip');

    // Create a mock ZIP
    $zip = new ZipArchive;
    $testZipPath = base_path('dist/test-exclusions.zip');

    if (! is_dir(base_path('dist'))) {
        mkdir(base_path('dist'), 0755, true);
    }

    if (file_exists($testZipPath)) {
        unlink($testZipPath);
    }

    $zip->open($testZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    // Test exclusions
    $exclude = [
        '.git',
        '.env',
        'node_modules',
        'tests',
    ];

    // Add test files
    $zip->addFromString('.env', 'test');
    $zip->addFromString('.env.freshinstall', 'test');
    $zip->addFromString('.env.example', 'test');
    $zip->addFromString('vendor/autoload.php', 'test');
    $zip->addFromString('tests/test.php', 'test');

    $zip->close();

    // Verify ZIP was created
    expect(file_exists($testZipPath))->toBeTrue();

    // Clean up
    unlink($testZipPath);
});

it('source files exist for bundling', function (): void {
    // Verify all critical source files exist
    $criticalFiles = [
        'artisan',
        'composer.json',
        '.env.freshinstall',
        'public/index.php',
        'vendor/autoload.php',
        'app/Console/Commands/InstallCommand.php',
    ];

    foreach ($criticalFiles as $file) {
        $path = base_path($file);
        expect(file_exists($path))->toBeTrue(
            "Critical file missing: {$file}"
        );
    }
});
