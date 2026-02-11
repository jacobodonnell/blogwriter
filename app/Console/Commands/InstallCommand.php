<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
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
        $this->welcome();

        if ($this->isAlreadyInstalled() && ! $this->option('force')) {
            warning('BlogWriter appears to already be installed.');

            $force = confirm(
                label: 'Do you want to force a fresh installation? This will delete existing data.',
                default: false
            );

            if (! $force) {
                info('Installation cancelled. Use --force to override.');

                return self::SUCCESS;
            }

            $this->freshInstall();
        }

        $config = $this->gatherConfiguration();
        $this->install($config);

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
        try {
            return User::exists();
        } catch (\Exception) {
            return false;
        }
    }

    protected function freshInstall(): void
    {
        info('Running fresh installation...');
        Artisan::call('migrate:fresh', ['--force' => true]);
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
        return strlen($value) >= 8 ? null : 'Password must be at least 8 characters.';
    }

    protected function promptForPassword(): string
    {
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

        // If max attempts reached, generate a secure password
        warning('Maximum attempts reached. Generating a secure password for you.');
        $password = \Illuminate\Support\Str::random(16);
        info("Your generated password: {$password}");

        return $password;
    }

    protected function install(array $config): void
    {
        $this->newLine();
        info('Installing BlogWriter...');
        $this->newLine();

        // Update .env file
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

        $this->newLine();
        $this->displaySuccess($config, $user);
    }

    protected function updateEnvironmentFile(array $config): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            warning('.env file not found. Skipping environment updates.');

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
}
