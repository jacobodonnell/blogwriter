<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\InstallService;
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

    public function __construct(protected InstallService $installService)
    {
        parent::__construct();
    }

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

        if ($this->installService->isAlreadyInstalled() && ! $this->option('force')) {
            warning('BlogWriter is already installed.');
            $this->newLine();

            $runReset = confirm(
                label: 'Do you want to reset BlogWriter first? (This will DELETE all content)',
                default: false
            );

            if ($runReset) {
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
            $this->installService->ensureStorageDirectories();
            $this->installService->createStorageLink();
            $this->installService->setupEnvironmentFile();
            $this->installService->generateAppKey();
            $this->installService->updateEnvironmentFile($config);

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

    protected function gatherConfiguration(): array
    {
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
        $siteName = $this->option('site-name');
        $siteUrl = $this->option('site-url');
        $name = $this->option('admin-name');
        $email = $this->option('admin-email');
        $password = $this->option('admin-password');

        if ($error = $this->validateUrl($siteUrl)) {
            throw new \InvalidArgumentException('Invalid site URL: '.$error);
        }

        if ($error = $this->validateEmail($email)) {
            throw new \InvalidArgumentException('Invalid admin email: '.$error);
        }

        if ($error = $this->validatePasswordLength($password)) {
            throw new \InvalidArgumentException('Invalid admin password: '.$error);
        }

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
        if (env('BYPASS_PASSWORD_RULES', false)) {
            return strlen($value) >= 8 ? null : 'Password must be at least 8 characters.';
        }

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
        $suggestedPassphrase = $this->installService->generatePassphrase();

        info('Suggested secure passphrase (memorable & strong):');
        info($suggestedPassphrase);
        $this->newLine();

        $useSuggested = confirm(
            label: 'Use this passphrase?',
            default: false
        );

        if ($useSuggested) {
            info('Your passphrase: '.$suggestedPassphrase);
            info('Please save this in a password manager!');
            $this->newLine();

            return $suggestedPassphrase;
        }

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

        warning('Maximum attempts reached. Generating a secure passphrase for you.');
        $generatedPassphrase = $this->installService->generatePassphrase();
        info('Your generated passphrase: '.$generatedPassphrase);
        info('Please save this in a password manager!');

        return $generatedPassphrase;
    }

    protected function install(array $config): void
    {
        $this->newLine();
        info('Installing BlogWriter...');
        $this->newLine();

        $this->installService->ensureStorageDirectories();
        info('✓ Storage directories ready');

        $this->installService->createStorageLink();
        info('✓ Storage link ready');

        info('Creating .env file from .env.example...');
        $this->installService->setupEnvironmentFile();
        info('✓ Environment file ready');

        info('Generating application key...');
        $this->installService->generateAppKey();
        info('✓ Application key generated');

        $this->installService->updateEnvironmentFile($config);
        info('✓ Environment configured');

        $this->installService->runMigrations();
        info('✓ Database migrated');

        $this->postMigrationSetup($config);
    }

    protected function postMigrationSetup(array $config): void
    {
        info('Creating admin user...');
        $user = $this->installService->createUser($config);
        info('✓ Admin user created');

        if ($config['seed']) {
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

        info('Clearing caches...');
        $this->installService->clearCaches();
        info('✓ Caches cleared');

        $this->installService->createLockFile();
        info('✓ Installation lock file created');

        $this->newLine();
        $this->displaySuccess($config, $user);
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
