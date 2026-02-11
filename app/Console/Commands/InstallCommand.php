<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PasswordGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class InstallCommand extends Command
{
    protected $signature = 'blogwriter:install
                            {--force : Force installation even if already installed}
                            {--seed : Seed with demo data after installation}';

    protected $description = 'Interactive installer for BlogWriter';

    public function handle(): int
    {
        // Check dependencies first
        if (! $this->hasVendorDirectory()) {
            if ($this->isComposerAvailable()) {
                if (! $this->runComposerInstall()) {
                    return self::FAILURE;
                }
            } else {
                error('Composer is not available and vendor directory is missing.');
                note('Please run `composer install` from the terminal before running this installer.');
                note('If you are on shared hosting, you may need to upload the vendor directory manually.');

                return self::FAILURE;
            }
        }

        // Check current prohibition state using reflection
        $reflection = new \ReflectionClass(\Illuminate\Database\Console\Migrations\FreshCommand::class);
        $property = $reflection->getProperty('prohibitedFromRunning');
        $wasProhibited = $property->getValue();

        // Disable destructive command prohibition for installer context
        \Illuminate\Support\Facades\DB::prohibitDestructiveCommands(false);

        try {
            return $this->runInstallation();
        } finally {
            // Restore original prohibition state
            \Illuminate\Support\Facades\DB::prohibitDestructiveCommands($wasProhibited);
        }
    }

    protected function runInstallation(): int
    {
        $this->welcome();

        $didFreshInstall = false;

        if ($this->isAlreadyInstalled() && ! $this->option('force')) {
            warning('BlogWriter appears to already be installed.');

            $override = confirm(
                label: 'Do you want to override the existing installation? ⚠️ This will DELETE all content and cannot be undone.',
                default: false
            );

            if (! $override) {
                info('Installation cancelled.');

                return self::SUCCESS;
            }

            // Delete lock file and reset database
            if (file_exists(storage_path('installed.lock'))) {
                unlink(storage_path('installed.lock'));
            }
            $this->freshInstall();
            $didFreshInstall = true;
        }

        // Only gather config and run install if we didn't just do a fresh install
        // freshInstall() already handles the full reset + config flow
        if (! $didFreshInstall) {
            $config = $this->gatherConfiguration();
            $this->install($config);
        }

        return self::SUCCESS;
    }

    protected function welcome(): void
    {
        info('╔════════════════════════════════════════╗');
        info('║     Welcome to BlogWriter Installer    ║');
        info('╚════════════════════════════════════════╝');
        note('This wizard will help you set up your IndieWeb-native blog.');
        $this->newLine();
    }

    protected function isAlreadyInstalled(): bool
    {
        // Check lock file first (primary check)
        if (file_exists(storage_path('installed.lock'))) {
            return true;
        }

        // Fallback to database check
        try {
            if (User::exists()) {
                // Create lock file for consistency (migration path)
                file_put_contents(storage_path('installed.lock'), now());

                return true;
            }
        } catch (\Exception) {
            return false;
        }

        return false;
    }

    protected function freshInstall(): void
    {
        info('Running fresh installation...');

        // Delete lock file to signal "installation in progress"
        if (file_exists(storage_path('installed.lock'))) {
            unlink(storage_path('installed.lock'));
            info('Removed installation lock.');
        }

        $dbPath = config('database.connections.sqlite.database');

        if (file_exists($dbPath)) {
            info('Removing existing database...');

            // Properly close all SQLite connections to release file locks
            DB::purge('sqlite');

            // Delete the database file
            if (! unlink($dbPath)) {
                throw new \RuntimeException("Cannot delete database file: {$dbPath}. Check file permissions.");
            }

            // Create fresh empty database file
            touch($dbPath);
            info('Fresh database created.');
        }

        // Reconnect to the database with the new file
        DB::reconnect('sqlite');

        info('Running database migrations...');
        $exitCode = Artisan::call('migrate', ['--force' => true]);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Database migration failed. Exit code: '.$exitCode);
        }

        info('Database reset complete.');
    }

    protected function gatherConfiguration(): array
    {
        info('Step 1: Site Configuration');
        $this->newLine();

        $siteName = text(
            label: 'What is your site name?',
            placeholder: 'E.g. My Awesome Blog',
            default: config('app.name', 'BlogWriter'),
            required: true
        );

        $siteUrl = text(
            label: 'What is your site URL?',
            placeholder: 'E.g. https://example.com',
            default: config('app.url', 'http://localhost'),
            required: true,
            validate: $this->validateUrl(...)
        );

        $this->newLine();
        info('Step 2: Admin User Setup');
        $this->newLine();

        $name = text(
            label: 'What is your name?',
            placeholder: 'E.g. Jane Doe',
            required: true
        );

        $email = text(
            label: 'What is your email address?',
            placeholder: 'E.g. you@example.com',
            required: true,
            validate: $this->validateEmail(...)
        );

        $password = $this->promptForPassword();

        $this->newLine();
        info('Step 3: Demo Content');
        $this->newLine();

        $seedData = confirm(
            label: 'Would you like to seed your blog with demo articles?',
            default: true
        );

        return [
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'seed' => $seedData,
        ];
    }

    protected function validateUrl(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) ? null : 'Please enter a valid URL.';
    }

    protected function validateEmail(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Please enter a valid email address.';
    }

    protected function validatePasswordLength(string $value): ?string
    {
        // Check bypass first
        if (env('BYPASS_PASSWORD_RULES', false)) {
            return strlen($value) >= 8 ? null : 'Password must be at least 8 characters.';
        }

        // Manual validation for CLI context (works without container)
        if (strlen($value) < 16) {
            return 'Password must be at least 16 characters.';
        }

        if (! preg_match('/[a-zA-Z]/', $value)) {
            return 'Password must contain at least one letter.';
        }

        if (! preg_match('/\d/', $value)) {
            return 'Password must contain at least one number.';
        }

        if (! preg_match('/[^a-zA-Z0-9]/', $value)) {
            return 'Password must contain at least one symbol.';
        }

        return null;
    }

    protected function promptForPassword(): string
    {
        $suggestedPassphrase = PasswordGenerator::generate();

        info('Suggested secure passphrase (memorable & strong):');
        info($suggestedPassphrase);
        $this->newLine();

        $useSuggested = confirm(
            label: 'Use this passphrase?',
            default: false
        );

        if ($useSuggested) {
            // Show it one more time for the user to copy
            info('Your passphrase: '.$suggestedPassphrase);
            info('Please save this in a password manager!');
            $this->newLine();

            return $suggestedPassphrase;
        }

        // Continue with existing manual password flow
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $password = password(
                label: 'Create a password',
                placeholder: 'Min 8 characters',
                validate: $this->validatePasswordLength(...)
            );

            $confirm = password(
                label: 'Confirm your password',
                placeholder: 'Enter the same password'
            );

            if ($password === $confirm) {
                return $password;
            }

            warning('Passwords do not match. Please try again.');
            $this->newLine();
            $attempts++;
        }

        // If max attempts reached, generate a secure passphrase
        warning('Maximum attempts reached. Generating a secure passphrase for you.');
        $generatedPassphrase = PasswordGenerator::generate();
        info("Your generated passphrase: {$generatedPassphrase}");
        info('Please save this in a password manager!');

        return $generatedPassphrase;
    }

    protected function install(array $config): void
    {
        $this->newLine();
        info('Installing BlogWriter...');
        $this->newLine();

        // Ensure storage directories exist before any Laravel operations
        $this->ensureStorageDirectories();

        // Create storage symlink for image uploads
        $this->createStorageLink();

        // 1. Setup .env file
        $this->setupEnvironmentFile();

        // 2. Generate APP_KEY
        $this->generateAppKey();

        // 3. Update .env with user config
        $this->updateEnvironmentFile($config);

        // Run migrations
        info('Running database migrations...');
        Artisan::call('migrate', ['--force' => true]);
        info('✓ Migrations complete');

        // Create admin user
        info('Creating admin user...');
        $user = $this->createUser($config);
        info('✓ Admin user created');

        // Seed demo data if requested
        if ($config['seed']) {
            info('Seeding demo content...');
            Artisan::call('blogwriter:seed', ['--state' => 'demo', '--no-interaction' => true]);
            info('✓ Demo content added');
        }

        // Clear caches
        info('Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        info('✓ Caches cleared');

        // Create installation lock file
        file_put_contents(storage_path('installed.lock'), now());
        info('✓ Installation lock file created');

        $this->newLine();
        $this->displaySuccess($config, $user);
    }

    protected function setupEnvironmentFile(): void
    {
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            return;
        }

        // Try .env.freshinstall first (for bundled installations)
        $envFreshPath = base_path('.env.freshinstall');
        if (file_exists($envFreshPath)) {
            info('Creating .env file from .env.freshinstall...');
            copy($envFreshPath, $envPath);
            info('✓ .env file created');

            return;
        }

        // Fall back to .env.example (for development environments)
        $envExamplePath = base_path('.env.example');
        if (file_exists($envExamplePath)) {
            info('Creating .env file from .env.example...');
            copy($envExamplePath, $envPath);
            info('✓ .env file created');

            return;
        }

        throw new \RuntimeException(
            'Cannot create .env file. Neither .env.freshinstall nor .env.example found. '.
            'Please ensure the BlogWriter distribution includes one of these template files.'
        );
    }

    protected function generateAppKey(): void
    {
        info('Generating application key...');
        $exitCode = Artisan::call('key:generate', ['--force' => true, '--no-interaction' => true]);

        if ($exitCode !== 0) {
            warning('Could not generate application key automatically.');
            info('You may need to run: php artisan key:generate');
        } else {
            info('✓ Application key generated');
        }
    }

    protected function updateEnvironmentFile(array $config): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        // Update APP_NAME
        $content = $this->updateEnvValue($content, 'APP_NAME', $config['site_name']);

        // Update APP_URL
        $content = $this->updateEnvValue($content, 'APP_URL', $config['site_url']);

        file_put_contents($envPath, $content);
    }

    protected function updateEnvValue(string $content, string $key, string $value): string
    {
        $escapedValue = str_replace('"', '\\"', $value);

        // Check if key exists
        if (preg_match("/^$key=/m", $content)) {
            // Update existing value
            $content = preg_replace(
                "/^$key=.*$/m",
                "$key=\"$escapedValue\"",
                $content
            );
        } else {
            // Add new key
            $content .= "\n$key=\"$escapedValue\"\n";
        }

        return $content;
    }

    protected function createUser(array $config): User
    {
        $user = User::create([
            'name' => $config['name'],
            'email' => $config['email'],
            'password' => Hash::make($config['password']),
        ]);

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    protected function displaySuccess(array $config, User $user): void
    {
        info('╔════════════════════════════════════════════════╗');
        info('║        Installation Complete! 🎉               ║');
        info('╚════════════════════════════════════════════════╝');
        $this->newLine();

        info('Your BlogWriter site is ready:');
        $this->newLine();

        $this->table(
            ['Setting', 'Value'],
            [
                ['Site Name', $config['site_name']],
                ['Site URL', $config['site_url']],
                ['Admin Email', $user->email],
                ['Demo Content', $config['seed'] ? 'Yes' : 'No'],
            ]
        );

        $this->newLine();
        info('Next steps:');
        note('  • Visit your site: '.$config['site_url']);
        note('  • Admin panel: '.rtrim((string) $config['site_url'], '/').'/admin');
        note('  • Login with: '.$user->email);
        $this->newLine();

        info('Happy blogging! Remember: own your content, own your domain.');
    }

    protected function isComposerAvailable(): bool
    {
        return ! (in_array(shell_exec('which composer'), ['', '0'], true) || shell_exec('which composer') === false || shell_exec('which composer') === null) || ! (in_array(shell_exec('where composer'), ['', '0'], true) || shell_exec('where composer') === false || shell_exec('where composer') === null);
    }

    protected function hasVendorDirectory(): bool
    {
        return is_dir(base_path('vendor'));
    }

    protected function runComposerInstall(): bool
    {
        info('Installing Composer dependencies...');

        $output = [];
        $returnCode = 0;
        exec('composer install --no-interaction --optimize-autoloader 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            info('✓ Composer dependencies installed');

            return true;
        }

        warning('Composer install failed. See error output above.');

        return false;
    }

    /**
     * Ensure required storage directories exist before installation proceeds.
     * This prevents Laravel errors when bundle extraction misses empty directories.
     */
    protected function ensureStorageDirectories(): void
    {
        $directories = [
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];

        foreach ($directories as $dir) {
            $path = base_path($dir);

            if (! is_dir($path)) {
                if (! mkdir($path, 0755, true)) {
                    throw new \RuntimeException("Failed to create directory: {$dir}. Check file permissions.");
                }
                info("✓ Created directory: {$dir}");
            } elseif (! is_writable($path)) {
                throw new \RuntimeException("Directory not writable: {$dir}. Check file permissions.");
            }
        }
    }

    /**
     * Create the storage symlink for public access to uploaded files.
     * This is required for featured images and other uploaded assets to be accessible.
     */
    protected function createStorageLink(): void
    {
        $publicStoragePath = public_path('storage');
        $targetPath = storage_path('app/public');

        // Check if symlink already exists
        if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
            info('✓ Storage link already exists');

            return;
        }

        // Ensure target directory exists
        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Create symlink
        if (@symlink($targetPath, $publicStoragePath)) {
            info('✓ Created storage symlink for public file access');
        } else {
            warning('Could not create storage symlink automatically');
            note('You may need to manually run: php artisan storage:link');
            note('Or on Windows, run as administrator: mklink /D public\\storage storage\\app\\public');
        }
    }
}
