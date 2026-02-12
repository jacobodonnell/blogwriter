<?php

use App\Models\User;

beforeEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

describe('non-interactive installation (arguments/flags)', function (): void {
    it('completes installation with all required arguments', function (): void {

        $this->artisan('blogwriter:install', [
            '--site-name' => 'My Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--no-seed' => true,
        ])->assertSuccessful();

        // Verify admin user created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        expect(User::count())->toBe(1);

        // Verify lock file created
        expect(file_exists(storage_path('installed.lock')))->toBeTrue();
    });

    it('seeds demo content when --seed flag is provided', function (): void {
        $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--seed' => true,
        ])->assertSuccessful();

        // Verify demo content was seeded
        expect(\App\Models\Category::count())->toBeGreaterThan(0);
        expect(\App\Models\Article::count())->toBeGreaterThan(0);
    })->group('slow');

    it('skips seeding when --no-seed flag is provided', function (): void {
        $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--no-seed' => true,
        ])->assertSuccessful();

        // Verify no demo content exists
        expect(\App\Models\Category::count())->toBe(0);
        expect(\App\Models\Article::count())->toBe(0);
    });

    it('validates site URL format', function (): void {
        expect(fn () => $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'not-a-valid-url',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
        ])->run())->toThrow(\InvalidArgumentException::class, 'Invalid site URL');
    });

    it('validates admin email format', function (): void {
        expect(fn () => $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'invalid-email',
            '--admin-password' => 'SecurePass123!@#456',
        ])->run())->toThrow(\InvalidArgumentException::class, 'Invalid admin email');
    });

    it('validates admin password requirements', function (): void {
        expect(fn () => $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'short',
        ])->run())->toThrow(\InvalidArgumentException::class, 'Invalid admin password');
    });

    it('bypasses already-installed check with --force flag', function (): void {
        // Create lock file to simulate already installed state
        file_put_contents(storage_path('installed.lock'), now());

        $this->artisan('blogwriter:install', [
            '--site-name' => 'Forced Install',
            '--site-url' => 'https://forced.com',
            '--admin-name' => 'Force User',
            '--admin-email' => 'force@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--force' => true,
            '--no-seed' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'force@example.com',
        ]);
    });

    it('updates APP_NAME and APP_URL in .env', function (): void {
        $this->artisan('blogwriter:install', [
            '--site-name' => 'Custom Blog Name',
            '--site-url' => 'https://custom.example.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--no-seed' => true,
        ])->assertSuccessful();

        $envContent = file_get_contents(base_path('.env'));
        expect($envContent)->toContain('APP_NAME="Custom Blog Name"');
        expect($envContent)->toContain('APP_URL="https://custom.example.com"');
    });
});

describe('interactive installation (prompts)', function (): void {
    it('completes fresh installation with valid inputs', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsOutputToContain('Welcome to BlogWriter Installer')
            ->expectsOutputToContain('Step 1: Site Configuration')
            ->expectsQuestion('What is your site name?', 'My Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://test.com')
            ->expectsOutputToContain('Step 2: Admin User Setup')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'no')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'SecurePass123!@#456')
            ->expectsOutputToContain('Step 3: Demo Content')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->expectsOutputToContain('Installation Complete!')
            ->assertSuccessful();

        // Verify admin user created
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        expect(User::count())->toBe(1);

        // Verify lock file created
        expect(file_exists(storage_path('installed.lock')))->toBeTrue();
    });

    it('accepts suggested passphrase', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://example.com')
            ->expectsQuestion('What is your name?', 'Jane Doe')
            ->expectsQuestion('What is your email address?', 'jane@example.com')
            ->expectsConfirmation('Use this passphrase?', 'yes')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->expectsOutputToContain('Installation Complete!')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);

        expect(User::count())->toBe(1);
    });

    it('retries on password mismatch', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://example.com')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'no')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'WrongPassword789')
            ->expectsOutputToContain('Passwords do not match')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'SecurePass123!@#456')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->expectsOutputToContain('Installation Complete!')
            ->assertSuccessful();

        expect(User::count())->toBe(1);
    });

    it('generates secure passphrase after max retry attempts', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://test.com')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'no')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'Wrong1')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'Wrong2')
            ->expectsQuestion('Create a password', 'SecurePass123!@#456')
            ->expectsQuestion('Confirm your password', 'Wrong3')
            ->expectsOutputToContain('Maximum attempts reached')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->assertSuccessful();

        expect(User::count())->toBe(1);
    });

    it('displays configuration summary table', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'My Blog')
            ->expectsQuestion('What is your site URL?', 'https://myblog.com')
            ->expectsQuestion('What is your name?', 'John Smith')
            ->expectsQuestion('What is your email address?', 'john@example.com')
            ->expectsConfirmation('Use this passphrase?', 'yes')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->expectsTable(
                headers: ['Setting', 'Value'],
                rows: [
                    ['Site Name', 'My Blog'],
                    ['Site URL', 'https://myblog.com'],
                    ['Admin Email', 'john@example.com'],
                    ['Demo Content', 'No'],
                ]
            )
            ->assertSuccessful();
    });

    it('seeds demo content when requested via prompt', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://test.com')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'yes')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', 'yes')
            ->expectsOutputToContain('Installation Complete!')
            ->assertSuccessful();

        // Verify demo content was seeded
        expect(\App\Models\Category::count())->toBeGreaterThan(0);
        expect(\App\Models\Article::count())->toBeGreaterThan(0);
    })->group('slow');

    it('skips seeding when declined via prompt', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://test.com')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'yes')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->doesntExpectOutput('Seeding demo content')
            ->expectsOutputToContain('Installation Complete!')
            ->assertSuccessful();

        // Verify no demo content exists
        expect(\App\Models\Category::count())->toBe(0);
        expect(\App\Models\Article::count())->toBe(0);
    });

    it('displays helpful next steps after installation', function (): void {
        $this->artisan('blogwriter:install')
            ->expectsQuestion('What is your site name?', 'Test Blog')
            ->expectsQuestion('What is your site URL?', 'https://test.com')
            ->expectsQuestion('What is your name?', 'Test User')
            ->expectsQuestion('What is your email address?', 'test@example.com')
            ->expectsConfirmation('Use this passphrase?', 'yes')
            ->expectsConfirmation('Would you like to seed your blog with demo articles?', false)
            ->expectsOutputToContain('Next steps:')
            ->expectsOutputToContain('Visit your site: https://test.com')
            ->expectsOutputToContain('Admin panel: https://test.com/admin')
            ->expectsOutputToContain('Login with: test@example.com')
            ->assertSuccessful();
    });
});

describe('already installed detection', function (): void {
    it('detects installation via lock file', function (): void {
        // Create lock file to simulate already installed state
        file_put_contents(storage_path('installed.lock'), now());

        $this->artisan('blogwriter:install')
            ->expectsOutputToContain('BlogWriter is already installed')
            ->expectsConfirmation('Do you want to reset BlogWriter first? (This will DELETE all content)', false)
            ->expectsOutputToContain('Installation cancelled')
            ->assertSuccessful();

        // Lock file should still exist
        expect(file_exists(storage_path('installed.lock')))->toBeTrue();

        // Clean up
        @unlink(storage_path('installed.lock'));
    });

    it('prompts for reset when already installed', function (): void {
        // Create lock file to simulate already installed state
        file_put_contents(storage_path('installed.lock'), now());

        $this->artisan('blogwriter:install')
            ->expectsOutputToContain('BlogWriter is already installed')
            ->expectsConfirmation('Do you want to reset BlogWriter first? (This will DELETE all content)', true)
            ->run();

        // The test verifies the prompt appears - full reset integration is complex
        // due to exec() calls in reset command and is better tested manually
    });
});

describe('environment configuration', function (): void {
    it('creates .env file from .env.example', function (): void {
        // Remove lock file and .env to ensure fresh install state
        @unlink(storage_path('installed.lock'));
        @unlink(base_path('.env'));

        $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--no-seed' => true,
        ])
            ->expectsOutputToContain('Creating .env file')
            ->assertSuccessful();

        // Verify .env was created
        expect(file_exists(base_path('.env')))->toBeTrue();
    });

    it('generates application key', function (): void {
        $this->artisan('blogwriter:install', [
            '--site-name' => 'Test Blog',
            '--site-url' => 'https://test.com',
            '--admin-name' => 'Test User',
            '--admin-email' => 'test@example.com',
            '--admin-password' => 'SecurePass123!@#456',
            '--no-seed' => true,
        ])
            ->expectsOutputToContain('Generating application key')
            ->assertSuccessful();

        // Verify APP_KEY is set in .env
        $envContent = file_get_contents(base_path('.env'));
        expect($envContent)->toContain('APP_KEY=base64:');
    });

    it('fails gracefully when .env.example is missing', function (): void {
        // Temporarily rename all .env files to simulate missing files
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');
        $envFreshPath = base_path('.env.freshinstall');
        $envBackupPath = base_path('.env.backup');
        $envExampleBackupPath = base_path('.env.example.backup');
        $freshBackupPath = base_path('.env.freshinstall.backup');

        $hasEnv = file_exists($envPath);
        $hasExample = file_exists($envExamplePath);
        $hasFresh = file_exists($envFreshPath);

        if ($hasEnv) {
            rename($envPath, $envBackupPath);
        }
        if ($hasExample) {
            rename($envExamplePath, $envExampleBackupPath);
        }
        if ($hasFresh) {
            rename($envFreshPath, $freshBackupPath);
        }

        try {
            // Expect a RuntimeException with helpful error message
            expect(fn () => $this->artisan('blogwriter:install', [
                '--site-name' => 'Test Blog',
                '--site-url' => 'https://test.com',
                '--admin-name' => 'Test User',
                '--admin-email' => 'test@example.com',
                '--admin-password' => 'SecurePass123!@#456',
                '--no-seed' => true,
            ])->run()
            )->toThrow(\RuntimeException::class, 'Cannot create .env file');
        } finally {
            // Restore files
            if ($hasEnv && file_exists($envBackupPath)) {
                rename($envBackupPath, $envPath);
            }
            if ($hasExample && file_exists($envExampleBackupPath)) {
                rename($envExampleBackupPath, $envExamplePath);
            }
            if ($hasFresh && file_exists($freshBackupPath)) {
                rename($freshBackupPath, $envFreshPath);
            }
        }
    });
});
