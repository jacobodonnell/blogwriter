<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PasswordGenerator;
use App\Services\ResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

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
                            {--site-name= : Site name}
                            {--site-url= : Site URL}
                            {--admin-name= : Admin user name}
                            {--admin-email= : Admin email address}
                            {--admin-password= : Admin password}
                            {--force : Force installation even if already installed}
                            {--seed : Seed with demo data after installation}
                            {--no-seed : Skip demo data seeding}';

    protected $description = 'Interactive installer for BlogWriter (supports non-interactive mode with flags)';

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
            warning('BlogWriter is already installed.');
            $this->newLine();

            $runReset = confirm(
                label: 'Do you want to reset BlogWriter first? (This will DELETE all content)',
                default: false
            );

            if ($runReset) {
                // Run reset using service
                $resetService = app(ResetService::class);
                $resetExitCode = $resetService->reset($this);

                if ($resetExitCode !== 0) {
                    error('Reset failed. Installation cancelled.');

                    return self::FAILURE;
                }

                $this->newLine();
                info('Reset complete. Continuing with installation...');
                $this->newLine();

                $didFreshInstall = true;
            } else {
                info('Installation cancelled.');

                return self::SUCCESS;
            }
        }

        $config = $this->gatherConfiguration();

        if (! $didFreshInstall) {
            $this->install($config);
        } else {
            // After reset: run .env setup (normally done in install())
            $this->ensureStorageDirectories();
            $this->createStorageLink();
            $this->setupEnvironmentFile();
            $this->generateAppKey();
            $this->updateEnvironmentFile($config);

            // Then run post-migration setup (user creation, seeding, lock file)
            $this->postMigrationSetup($config);
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

    protected function gatherConfiguration(): array
    {
        // Check if running in non-interactive mode (all required options provided)
        if ($this->isNonInteractive()) {
            return $this->gatherNonInteractiveConfiguration();
        }

        return $this->gatherInteractiveConfiguration();
    }

    protected function isNonInteractive(): bool
    {
        return $this->option('site-name')
            && $this->option('site-url')
            && $this->option('admin-name')
            && $this->option('admin-email')
            && $this->option('admin-password');
    }

    protected function gatherNonInteractiveConfiguration(): array
    {
        // Validate all inputs
        $siteName = $this->option('site-name');
        $siteUrl = $this->option('site-url');
        $name = $this->option('admin-name');
        $email = $this->option('admin-email');
        $password = $this->option('admin-password');

        // Validate URL
        if ($error = $this->validateUrl($siteUrl)) {
            throw new \InvalidArgumentException('Invalid site URL: '.$error);
        }

        // Validate email
        if ($error = $this->validateEmail($email)) {
            throw new \InvalidArgumentException('Invalid admin email: '.$error);
        }

        // Validate password
        if ($error = $this->validatePasswordLength($password)) {
            throw new \InvalidArgumentException('Invalid admin password: '.$error);
        }

        // Determine seed option
        $seed = $this->determineSeedOption();

        return [
            'site_name' => $siteName,
            'site_url' => $siteUrl,
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'seed' => $seed,
        ];
    }

    protected function gatherInteractiveConfiguration(): array
    {
        info('Step 1: Site Configuration');
        $this->newLine();

        $siteName = $this->option('site-name') ?? text(
            label: 'What is your site name?',
            placeholder: 'E.g. My Awesome Blog',
            default: config('app.name', 'BlogWriter'),
            required: true
        );

        $siteUrl = $this->option('site-url') ?? text(
            label: 'What is your site URL?',
            placeholder: 'E.g. https://example.com',
            default: config('app.url', 'http://localhost'),
            required: true,
            validate: $this->validateUrl(...)
        );

        $this->newLine();
        info('Step 2: Admin User Setup');
        $this->newLine();

        $name = $this->option('admin-name') ?? text(
            label: 'What is your name?',
            placeholder: 'E.g. Jane Doe',
            required: true
        );

        $email = $this->option('admin-email') ?? text(
            label: 'What is your email address?',
            placeholder: 'E.g. you@example.com',
            required: true,
            validate: $this->validateEmail(...)
        );

        $password = $this->option('admin-password') ?? $this->promptForPassword();

        $this->newLine();
        info('Step 3: Demo Content');
        $this->newLine();

        $seedData = $this->determineSeedOption();

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
        info('Your generated passphrase: '.$generatedPassphrase);
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
        $this->freshInstall();

        // Complete post-migration setup
        $this->postMigrationSetup($config);
    }

    /**
     * Complete setup after migrations: create user, seed data, lock file.
     * Called either from install() or after reset + freshInstall().
     */
    protected function postMigrationSetup(array $config): void
    {
        // Create admin user
        info('Creating admin user...');
        $user = $this->createUser($config);
        info('✓ Admin user created');

        // Seed demo data if requested
        if ($config['seed']) {
            // Clear any leftover temp files from previous failed attempts
            \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('media-library/temp');

            // Pre-flight check for demo images
            info('Verifying demo images...');
            $imagesValid = $this->verifyDemoImages();
            if (! $imagesValid) {
                info('⚠️  Proceeding with seeding, but some images may be skipped.');
            }

            info('Seeding demo content...');
            info('Processing demo images and content (this may take 30-60 seconds)...');
            $this->newLine();
            Artisan::call('blogwriter:seed', ['--state' => 'demo', '--no-interaction' => true], $this->output);
            $this->newLine();
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

    /**
     * Placeholder method called after reset.
     * ResetService already handles migrations, so this is a no-op.
     */
    protected function freshInstall(): void
    {
        // ResetService already ran migrations - nothing to do here
    }

    protected function setupEnvironmentFile(): void
    {
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            return;
        }

        $envExamplePath = base_path('.env.example');
        if (file_exists($envExamplePath)) {
            info('Creating .env file from .env.example...');
            copy($envExamplePath, $envPath);
            info('✓ .env file created');

            return;
        }

        throw new \RuntimeException(
            'Cannot create .env file. .env.example not found. '.
            'Please ensure the BlogWriter distribution includes this template file.'
        );
    }

    protected function generateAppKey(): void
    {
        info('Generating application key...');

        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            warning('Cannot generate key: .env file missing');

            return;
        }

        // Generate AES-256-CBC key (32 bytes base64 encoded)
        $key = 'base64:'.base64_encode(random_bytes(32));

        // Update .env file
        $content = file_get_contents($envPath);
        $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content);
        file_put_contents($envPath, $content);

        info('✓ Application key generated');
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
        if (preg_match(sprintf('/^%s=/m', $key), $content)) {
            // Update existing value
            $content = preg_replace(
                sprintf('/^%s=.*$/m', $key),
                sprintf('%s="%s"', $key, $escapedValue),
                $content
            );
        } else {
            // Add new key
            $content .= "\n{$key}=\"{$escapedValue}\"\n";
        }

        return $content;
    }

    protected function createUser(array $config): User
    {
        // Single-user system: replace existing user if present
        User::query()->delete();

        return User::create([
            'name' => $config['name'],
            'email' => $config['email'],
            'password' => $config['password'],
            'email_verified_at' => now(),
        ]);
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
        if ($this->commandExists('which composer')) {
            return true;
        }

        return $this->commandExists('where composer');
    }

    private function commandExists(string $command): bool
    {
        $output = shell_exec($command);

        return ! in_array($output, ['', '0', false, null], true) && $output !== '0';
    }

    protected function determineSeedOption(): bool
    {
        if ($this->option('seed')) {
            return true;
        }

        if ($this->option('no-seed')) {
            return false;
        }

        // Prompt in interactive mode, default to true in non-interactive
        if (! $this->option('no-interaction')) {
            return confirm(
                label: 'Would you like to seed your blog with demo articles?',
                default: false,
                hint: 'Demo content includes sample articles, photos, and categories'
            );
        }

        return true;
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
                    throw new \RuntimeException(sprintf('Failed to create directory: %s. Check file permissions.', $dir));
                }

                info('✓ Created directory: '.$dir);
            } elseif (! is_writable($path)) {
                throw new \RuntimeException(sprintf('Directory not writable: %s. Check file permissions.', $dir));
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

    /**
     * Verify demo images exist and are valid before seeding.
     * Prevents installer hang from missing or corrupted image files.
     */
    protected function verifyDemoImages(): bool
    {
        $demoImagesPath = database_path('seeders/demo-images');
        $requiredImages = [
            'demo-image-1.png',
            'demo-image-2.png',
            'demo-image-3.png',
            'demo-image-4.png',
            'demo-image-5.png',
        ];

        $missingOrInvalid = [];

        foreach ($requiredImages as $image) {
            $imagePath = $demoImagesPath.'/'.$image;

            if (! file_exists($imagePath)) {
                $missingOrInvalid[] = $image.' (missing)';
            } elseif (filesize($imagePath) === 0) {
                $missingOrInvalid[] = $image.' (0 bytes)';
            }
        }

        if ($missingOrInvalid !== []) {
            warning('⚠️  Demo image issues detected:');
            foreach ($missingOrInvalid as $issue) {
                warning('  - '.$issue);
            }

            warning('Seeding will skip featured images for affected articles.');
            $this->newLine();

            return false;
        }

        return true;
    }
}
