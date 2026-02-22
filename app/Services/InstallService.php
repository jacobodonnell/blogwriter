<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

final class InstallService
{
    /**
     * Check if BlogWriter is already installed.
     *
     * When a lock file exists, verifies the database is healthy before
     * trusting it. Stale locks (missing/empty DB) are removed.
     */
    public function isAlreadyInstalled(): bool
    {
        if (file_exists(storage_path('installed.lock'))) {
            if ($this->isDatabaseHealthy()) {
                return true;
            }

            // Stale lock — DB is missing or corrupt
            @unlink(storage_path('installed.lock'));

            return false;
        }

        try {
            if (User::exists()) {
                file_put_contents(storage_path('installed.lock'), now());

                return true;
            }
        } catch (Exception) {
            return false;
        }

        return false;
    }

    /**
     * Ensure the SQLite database file exists, cleaning up orphaned WAL files.
     *
     * Call this before running migrations to prevent "database is locked" errors
     * when stale -wal/-shm files remain after the main .sqlite file was deleted.
     */
    public function ensureDatabaseFile(): void
    {
        $dbPath = config('database.connections.sqlite.database');

        if ($dbPath === ':memory:' || file_exists($dbPath)) {
            return;
        }

        // Clean up orphaned WAL files
        foreach (['-wal', '-shm'] as $suffix) {
            $walFile = $dbPath.$suffix;
            if (file_exists($walFile)) {
                @unlink($walFile);
            }
        }

        if (! touch($dbPath)) {
            throw new RuntimeException(sprintf('Failed to create database file: %s. Check file permissions.', $dbPath));
        }
    }

    /**
     * Check system requirements and return structured results.
     *
     * @return array{requirements: array<int, array{name: string, passed: bool, value: string}>, allPassed: bool}
     */
    public function checkRequirements(): array
    {
        $requirements = [
            [
                'name' => 'PHP 8.4+',
                'passed' => PHP_VERSION_ID >= 80400,
                'value' => PHP_VERSION,
            ],
            [
                'name' => 'SQLite Extension',
                'passed' => extension_loaded('pdo_sqlite'),
                'value' => extension_loaded('pdo_sqlite') ? 'Enabled' : 'Missing',
            ],
            [
                'name' => 'PDO Extension',
                'passed' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Enabled' : 'Missing',
            ],
            [
                'name' => 'Fileinfo Extension',
                'passed' => extension_loaded('fileinfo'),
                'value' => extension_loaded('fileinfo') ? 'Enabled' : 'Missing',
            ],
            [
                'name' => 'Storage Writable',
                'passed' => is_writable(storage_path()),
                'value' => is_writable(storage_path()) ? 'OK' : 'Not Writable',
            ],
            [
                'name' => 'Cache Writable',
                'passed' => is_writable(base_path('bootstrap/cache')),
                'value' => is_writable(base_path('bootstrap/cache')) ? 'OK' : 'Not Writable',
            ],
        ];

        $allPassed = collect($requirements)->every(fn (array $req): bool => $req['passed']);

        return [
            'requirements' => $requirements,
            'allPassed' => $allPassed,
        ];
    }

    /**
     * Ensure required storage directories exist.
     */
    public function ensureStorageDirectories(): void
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
                    throw new RuntimeException(sprintf('Failed to create directory: %s. Check file permissions.', $dir));
                }
            } elseif (! is_writable($path)) {
                throw new RuntimeException(sprintf('Directory not writable: %s. Check file permissions.', $dir));
            }
        }
    }

    /**
     * Create the storage symlink for public file access.
     */
    public function createStorageLink(): void
    {
        $publicStoragePath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
            return;
        }

        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        @symlink($targetPath, $publicStoragePath);
    }

    /**
     * Copy .env.example to .env if it doesn't exist.
     */
    public function setupEnvironmentFile(): void
    {
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            return;
        }

        $envExamplePath = base_path('.env.example');
        if (file_exists($envExamplePath)) {
            copy($envExamplePath, $envPath);

            return;
        }

        throw new RuntimeException(
            'Cannot create .env file. .env.example not found. '.
            'Please ensure the BlogWriter distribution includes this template file.'
        );
    }

    /**
     * Generate a new APP_KEY in the .env file.
     */
    public function generateAppKey(): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $key = 'base64:'.base64_encode(random_bytes(32));

        $content = file_get_contents($envPath);
        $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content);
        file_put_contents($envPath, $content);
    }

    /**
     * Update .env file with site configuration values.
     */
    public function updateEnvironmentFile(array $config): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        if (isset($config['site_name'])) {
            $content = $this->updateEnvValue($content, 'APP_NAME', $config['site_name']);
        }

        if (isset($config['site_url'])) {
            $content = $this->updateEnvValue($content, 'APP_URL', $config['site_url']);
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Run database migrations.
     */
    public function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
    }

    /**
     * Create the admin user.
     */
    public function createUser(array $config): User
    {
        User::query()->delete();

        return User::create([
            'name' => $config['name'],
            'email' => $config['email'],
            'password' => $config['password'],
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Clear application caches.
     */
    public function clearCaches(): void
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    /**
     * Create the installation lock file.
     */
    public function createLockFile(): void
    {
        file_put_contents(storage_path('installed.lock'), now());
    }

    /**
     * Check if the SQLite database file exists, is non-empty, and queryable.
     */
    private function isDatabaseHealthy(): bool
    {
        $dbPath = config('database.connections.sqlite.database');

        // In-memory databases are always considered healthy if queryable
        if ($dbPath !== ':memory:' && (! file_exists($dbPath) || filesize($dbPath) === 0)) {
            return false;
        }

        try {
            User::exists();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Update or add an environment variable value.
     */
    private function updateEnvValue(string $content, string $key, string $value): string
    {
        $escapedValue = str_replace('"', '\\"', $value);

        if (preg_match(sprintf('/^%s=/m', $key), $content)) {
            $content = preg_replace(
                sprintf('/^%s=.*$/m', $key),
                sprintf('%s="%s"', $key, $escapedValue),
                $content
            );
        } else {
            $content .= "\n{$key}=\"{$escapedValue}\"\n";
        }

        return $content;
    }
}
