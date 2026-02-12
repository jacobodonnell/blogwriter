<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class BlogWriterReset extends Command
{
    protected $signature = 'blogwriter:reset {--force : Skip confirmation}';

    protected $description = 'Reset BlogWriter to fresh state (wipes all data and media)';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $confirmed = confirm(
                label: '⚠️  This will DELETE all content and media. Continue?',
                default: false
            );

            if (! $confirmed) {
                info('Reset cancelled.');

                return self::SUCCESS;
            }
        }

        warning('Resetting BlogWriter...');
        $this->newLine();

        // Disable destructive command prohibition
        DB::prohibitDestructiveCommands(false);

        try {
            // 1. Remove installation lock
            info('[1/5] Removing installation lock...');
            if (file_exists(storage_path('installed.lock'))) {
                unlink(storage_path('installed.lock'));
            }
            info('  ✓ Lock removed');

            // 2. Wipe database
            info('[2/5] Wiping database...');
            $dbPath = config('database.connections.sqlite.database');

            if (file_exists($dbPath)) {
                // Close all connections properly
                DB::disconnect('sqlite');
                DB::purge('sqlite');

                // Force garbage collection to release any PHP references
                gc_collect_cycles();

                // Wait a moment for locks to clear
                usleep(250000); // 250ms

                // Delete database file and WAL files (for WAL mode)
                $filesToDelete = [
                    $dbPath,
                    $dbPath.'-wal',
                    $dbPath.'-shm',
                ];

                foreach ($filesToDelete as $file) {
                    if (! file_exists($file)) {
                        continue;
                    }
                    if (unlink($file)) {
                        continue;
                    }
                    throw new \RuntimeException("Failed to delete file: {$file}. File may be locked.");
                }

                // Create fresh empty database
                touch($dbPath);
            }

            // Reconnect and run migrations using exec to avoid deadlocks
            DB::reconnect('sqlite');
            exec('php artisan migrate --force --no-interaction 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \RuntimeException('Migration failed: '.implode("\n", $output));
            }

            info('  ✓ Database reset');

            // 3. Clear all storage (public and private)
            info('[3/5] Clearing storage...');
            Storage::disk('public')->deleteDirectory('');
            Storage::disk('private')->deleteDirectory('');
            info('  ✓ Storage cleared');

            // 4. Recreate storage structure
            info('[4/5] Recreating storage structure...');
            exec('php artisan storage:link --force 2>&1', $output, $returnCode);
            if ($returnCode !== 0) {
                warning('  ! Storage link failed (may already exist)');
            } else {
                info('  ✓ Storage linked');
            }

            // 5. Clear caches
            info('[5/5] Clearing caches...');
            exec('php artisan config:clear 2>&1');
            exec('php artisan cache:clear 2>&1');
            info('  ✓ Caches cleared');

            $this->newLine();
            info('✅ BlogWriter reset complete!');
            info('   Database: Fresh (empty)');
            info('   Storage: Empty');
            info('   Status: Ready for installation');
            $this->newLine();
            info('Run php artisan blogwriter:install to set up BlogWriter again.');

            return self::SUCCESS;

        } finally {
            DB::prohibitDestructiveCommands(true);
        }
    }
}
