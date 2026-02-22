<?php

declare(strict_types=1);

use App\Services\ResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

describe('ResetService', function (): void {
    beforeEach(function (): void {
        $lockPath = storage_path('installed.lock');
        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        // Use a temp file instead of :memory: so touch()/unlink() don't
        // create stray files in the project root
        $this->tempDb = tempnam(sys_get_temp_dir(), 'bw_reset_test_');
        config(['database.connections.sqlite.database' => $this->tempDb]);

        $this->command = Mockery::mock(Command::class);
        $this->command->shouldReceive('newLine')->atLeast()->once();

        DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);
        DB::shouldReceive('disconnect')->andReturn(null);
        DB::shouldReceive('purge')->andReturn(null);
        DB::shouldReceive('reconnect')->andReturn(null);
    });

    afterEach(function (): void {
        @unlink($this->tempDb);
        @unlink($this->tempDb.'-wal');
        @unlink($this->tempDb.'-shm');
    });

    it('removes installation lock file when it exists', function (): void {
        $lockPath = storage_path('installed.lock');
        file_put_contents($lockPath, now());

        expect(file_exists($lockPath))->toBeTrue();

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $service->reset($this->command);

        expect(file_exists($lockPath))->toBeFalse();
    });

    it('handles missing lock file gracefully', function (): void {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $exitCode = $service->reset($this->command);

        expect($exitCode)->toBe(Command::SUCCESS);
    });

    it('returns success exit code on successful reset', function (): void {
        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $exitCode = $service->reset($this->command);

        expect($exitCode)->toBe(Command::SUCCESS);
    });

    it('calls deleteDirectory on public and private disks', function (): void {
        Storage::shouldReceive('disk')
            ->with('public')
            ->once()
            ->andReturnSelf();

        Storage::shouldReceive('disk')
            ->with('private')
            ->once()
            ->andReturnSelf();

        Storage::shouldReceive('deleteDirectory')
            ->with('')
            ->twice()
            ->andReturn(true);

        $service = new ResetService;
        $service->reset($this->command);
    });

    it('cleans orphaned WAL files when main sqlite is missing', function (): void {
        $dbPath = config('database.connections.sqlite.database');

        // Ensure main db doesn't exist but WAL files do
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        file_put_contents($dbPath.'-wal', 'orphaned');
        file_put_contents($dbPath.'-shm', 'orphaned');

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $exitCode = $service->reset($this->command);

        expect($exitCode)->toBe(Command::SUCCESS)
            ->and(file_exists($dbPath.'-wal'))->toBeFalse()
            ->and(file_exists($dbPath.'-shm'))->toBeFalse()
            ->and(file_exists($dbPath))->toBeTrue();
    });

    it('returns failure exit code when exception occurs', function (): void {
        Storage::shouldReceive('disk')->andThrow(new RuntimeException('Storage failure'));

        $service = new ResetService;
        $exitCode = $service->reset($this->command);

        expect($exitCode)->toBe(Command::FAILURE);
    });
});
