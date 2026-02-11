<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class DiagnoseCommand extends Command
{
    protected $signature = 'blogwriter:diagnose';

    protected $description = 'Diagnose common BlogWriter installation issues';

    public function handle(): int
    {
        $this->info('🔍 BlogWriter Diagnostic Tool');
        $this->newLine();

        $issues = [];

        // Check 1: .env file exists
        $this->check('ENV file exists', function () {
            return file_exists(base_path('.env'));
        }, '.env file is missing - run php artisan blogwriter:install');

        // Check 2: Database exists
        $this->check('Database file exists', function () {
            $dbPath = config('database.connections.sqlite.database');

            return file_exists($dbPath);
        }, 'Database file missing at '.database_path('database.sqlite'));

        // Check 3: Database has tables
        $this->check('Database has tables', function () {
            try {
                DB::select('SELECT 1 FROM users LIMIT 1');

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }, 'Database tables missing - run php artisan migrate');

        // Check 4: Controllers exist
        $this->check('Controllers exist', function () {
            return file_exists(app_path('Http/Controllers/Admin/SettingsController.php'));
        }, 'SettingsController.php is missing - re-install from fresh bundle');

        // Check 5: Routes registered
        $this->check('Routes cached', function () {
            try {
                Route::getRoutes()->refreshNameLookups();

                return Route::has('admin.settings');
            } catch (\Exception $e) {
                return false;
            }
        }, 'Routes not properly registered - run php artisan route:clear');

        // Check 6: Storage permissions
        $this->check('Storage writable', function () {
            return is_writable(storage_path('logs'));
        }, 'storage/logs is not writable - run: chmod -R 775 storage');

        // Check 7: Vendor exists
        $this->check('Composer dependencies', function () {
            return file_exists(base_path('vendor/autoload.php'));
        }, 'vendor/autoload.php missing - run: composer install');

        // Check 8: App key set
        $this->check('APP_KEY set', function () {
            return ! empty(env('APP_KEY')) && env('APP_KEY') !== 'base64:';
        }, 'APP_KEY is not set - run: php artisan key:generate');

        $this->newLine();
        $this->info('📊 Diagnostic complete!');
        $this->newLine();

        // Show recent errors if log exists
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath) && filesize($logPath) > 0) {
            $this->warn('⚠️  Recent errors found in log:');
            $lines = array_slice(file($logPath), -20);
            foreach ($lines as $line) {
                if (str_contains($line, 'ERROR')) {
                    $this->error(trim($line));
                }
            }
        }

        return self::SUCCESS;
    }

    protected function check(string $name, callable $test, string $fix): void
    {
        try {
            $result = $test();
            if ($result) {
                $this->info("✓ {$name}");
            } else {
                $this->error("✗ {$name}");
                $this->warn("  Fix: {$fix}");
            }
        } catch (\Exception $e) {
            $this->error("✗ {$name} - Exception: ".$e->getMessage());
            $this->warn("  Fix: {$fix}");
        }
    }
}
