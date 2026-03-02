<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Resettable;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

final class ResetService implements Resettable
{
    public function reset(Command $command): int
    {
        warning('Resetting BlogWriter...');
        $command->newLine();

        // Disable destructive command prohibition
        DB::prohibitDestructiveCommands(false);

        try {
            // 1. Remove installation lock
            info('[1/4] Removing installation lock...');
            if (file_exists(storage_path('installed.lock'))) {
                unlink(storage_path('installed.lock'));
            }

            info('  ✓ Lock removed');

            // 2. Wipe database
            info('[2/4] Wiping database...');
            $dbPath = config('database.connections.sqlite.database');

            // Close all connections properly
            DB::disconnect('sqlite');
            DB::purge('sqlite');

            // Force garbage collection to release any PHP references
            gc_collect_cycles();

            // SQLite locks are released asynchronously at the OS level after
            // disconnect + purge. A brief wait prevents "database is locked"
            // errors when deleting the file immediately after.
            usleep(250000); // 250ms

            // Delete database file and WAL files (for WAL mode)
            // WAL cleanup runs even if the main .sqlite is already gone
            $filesToDelete = [
                $dbPath,
                $dbPath.'-wal',
                $dbPath.'-shm',
            ];

            foreach ($filesToDelete as $file) {
                if (! file_exists($file)) {
                    continue;
                }

                // Symlink-safe: operate on the real target so Forge shared
                // paths survive the reset.
                $target = is_link($file) ? readlink($file) : $file;

                if (unlink($target)) {
                    continue;
                }

                throw new RuntimeException(sprintf('Failed to delete file: %s. File may be locked.', $file));
            }

            // Create fresh empty database (touch the real target if symlinked)
            $touchTarget = is_link($dbPath) ? readlink($dbPath) : $dbPath;
            if (! touch($touchTarget)) {
                throw new RuntimeException(sprintf('Failed to create database file: %s. Check file permissions.', $dbPath));
            }

            info('  ✓ Database reset');

            // 3. Clear all storage (public and private)
            info('[3/4] Clearing storage...');
            Storage::disk('public')->deleteDirectory('');
            Storage::disk('private')->deleteDirectory('');
            info('  ✓ Storage cleared');

            // 4. Ready for caller to run migrations and re-seed
            info('[4/4] Ready for reinstallation...');

            $command->newLine();
            info('✅ BlogWriter reset complete!');
            info('   Database: Fresh (empty)');
            info('   Storage: Empty');
            info('   Status: Ready for installation');

            return Command::SUCCESS;

        } catch (Exception $exception) {
            $command->newLine();
            warning('❌ Reset failed: '.$exception->getMessage());

            return Command::FAILURE;

        } finally {
            DB::prohibitDestructiveCommands(true);
        }
    }
}
