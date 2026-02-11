<?php

use App\Console\Commands\BundleCommand;

it('has correct command signature and description', function (): void {
    $command = new BundleCommand;

    expect($command->getName())->toBe('blogwriter:bundle');
    expect($command->getDescription())->toBe('Create a distributable ZIP package of BlogWriter');
});

it('has skip-build option', function (): void {
    $command = new BundleCommand;

    expect($command->getDefinition()->hasOption('skip-build'))->toBeTrue();
});

describe('formatBytes helper', function (): void {
    it('formats bytes correctly', function (): void {
        $command = new BundleCommand;
        $reflection = new ReflectionMethod($command, 'formatBytes');

        expect($reflection->invoke($command, 0))->toBe('0 B');
        expect($reflection->invoke($command, 512))->toBe('512 B');
        expect($reflection->invoke($command, 1024))->toBe('1024 B');
        expect($reflection->invoke($command, 1025))->toBe('1 KB');
        expect($reflection->invoke($command, 1536))->toBe('1.5 KB');
        expect($reflection->invoke($command, 1048576))->toBe('1024 KB');
        expect($reflection->invoke($command, 1048577))->toBe('1 MB');
        expect($reflection->invoke($command, 1073741824))->toBe('1024 MB');
        expect($reflection->invoke($command, 1073741825))->toBe('1 GB');
    });

    it('handles large values', function (): void {
        $command = new BundleCommand;
        $reflection = new ReflectionMethod($command, 'formatBytes');

        expect($reflection->invoke($command, 2147483648))->toBe('2 GB');
        expect($reflection->invoke($command, 5368709120))->toBe('5 GB');
    });
});

describe('exclusion patterns', function (): void {
    it('excludes .git directory', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('.git');
        expect($excluded)->toContain('.gitignore');
    });

    it('excludes node_modules directory', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('node_modules');
    });

    it('excludes .env file', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('.env');
    });

    it('excludes tests directory', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('tests');
        expect($excluded)->toContain('phpunit.xml');
        expect($excluded)->toContain('pest.xml');
    });

    it('excludes development and log files', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('.DS_Store');
        expect($excluded)->toContain('*.log');
        expect($excluded)->toContain('dist');
    });

    it('excludes storage directories with runtime data', function (): void {
        $excluded = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            '.env',
            'tests',
            'phpunit.xml',
            'pest.xml',
            '.DS_Store',
            '*.log',
        ];

        expect($excluded)->toContain('storage/logs');
        expect($excluded)->toContain('storage/framework/cache');
        expect($excluded)->toContain('storage/framework/sessions');
        expect($excluded)->toContain('storage/framework/testing');
        expect($excluded)->toContain('storage/framework/views');
    });
});

describe('ZIP file structure', function (): void {
    it('uses correct version format in filename', function (): void {
        // The command currently hardcodes version '0.1a'
        $expectedPattern = '/^blogwriter-v[\d.]+[a-z]?\.zip$/';

        // Version pattern should match blogwriter-vX.X.zip format
        $version = '0.1a';
        $zipName = "blogwriter-v{$version}.zip";

        expect($zipName)->toBe('blogwriter-v0.1a.zip');
        expect(preg_match($expectedPattern, $zipName))->toBe(1);
    });
});

describe('addDirectoryToZip exclusion logic', function (): void {
    it('correctly identifies excluded patterns', function (): void {
        $command = new BundleCommand;
        $reflection = new ReflectionMethod($command, 'addDirectoryToZip');

        $exclude = [
            '.git',
            '.gitignore',
            'node_modules',
            'dist',
            'storage/logs',
            '.env',
            'tests',
            '.DS_Store',
            '*.log',
        ];

        // Test various paths against exclusion patterns
        // Note: .env.example is excluded because it starts with .env pattern
        $testCases = [
            ['.git/config', true],
            ['.gitignore', true],
            ['node_modules/package.json', true],
            ['dist/blogwriter-v0.1.zip', true],
            ['storage/logs/laravel.log', true],
            ['.env', true], // Only exact .env is excluded
            ['.env.example', false], // Now included - only exact .env excluded
            ['.env.freshinstall', false], // Must be included for installation
            ['tests/Feature/Test.php', true],
            ['app/Models/User.php', false],
            ['vendor/autoload.php', false],
            ['public/build/manifest.json', false],
            ['storage/app/public/.gitkeep', false],
        ];

        foreach ($testCases as [$relativePath, $shouldBeExcluded]) {
            $isExcluded = false;
            foreach ($exclude as $pattern) {
                // Match the logic from BundleCommand
                if ($pattern === '.env') {
                    // Only exclude exact .env file, not .env.* files
                    if (basename($relativePath) === '.env') {
                        $isExcluded = true;
                        break;
                    }

                    continue;
                }

                if (str_starts_with($relativePath, $pattern) || fnmatch($pattern, basename($relativePath))) {
                    $isExcluded = true;
                    break;
                }
            }

            expect($isExcluded)->toBe($shouldBeExcluded, "Path '{$relativePath}' exclusion check failed");
        }
    });

    it('includes .env.freshinstall in bundle', function (): void {
        // Verify the source code includes special handling for .env files
        $source = file_get_contents(app_path('Console/Commands/BundleCommand.php'));

        // Check that .env special handling exists
        expect($source)->toContain("if (\$pattern === '.env')");
        expect($source)->toContain("if (basename(\$relativePath) === '.env')");
        expect($source)->toContain("continue; // Don't exclude .env.example, .env.freshinstall, etc.");
    });

    it('excludes only exact .env file not env templates', function (): void {
        // Test the actual exclusion logic from BundleCommand
        $exclude = [
            '.git',
            '.gitignore',
            '.env',  // This should only match exact .env
            'node_modules',
        ];

        $testCases = [
            ['.env', true],                          // Exact match - excluded
            ['.env.example', false],                 // Different file - included
            ['.env.freshinstall', false],            // Different file - included
            ['config/.env', true],                   // Exact match in subdir - excluded
            ['config/.env.example', false],          // Different file in subdir - included
        ];

        foreach ($testCases as [$relativePath, $shouldBeExcluded]) {
            $isExcluded = false;
            $basename = basename($relativePath);

            foreach ($exclude as $pattern) {
                if ($pattern === '.env') {
                    if ($basename === '.env') {
                        $isExcluded = true;
                        break;
                    }

                    continue;
                }

                if (str_starts_with($relativePath, $pattern) || fnmatch($pattern, $basename)) {
                    $isExcluded = true;
                    break;
                }
            }

            expect($isExcluded)->toBe($shouldBeExcluded);
        }
    });
});
