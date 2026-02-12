<?php

use App\Services\ResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

describe('ResetService', function (): void {
    it('removes installation lock file when it exists', function (): void {
        $lockPath = storage_path('installed.lock');
        file_put_contents($lockPath, now());

        expect(file_exists($lockPath))->toBeTrue();

        $command = \Mockery::mock(Command::class);
        $command->shouldReceive('newLine')->atLeast()->once();

        // Mock the database operations to prevent conflicts with test database
        \DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);
        \DB::shouldReceive('disconnect')->andReturn(null);
        \DB::shouldReceive('purge')->andReturn(null);
        \DB::shouldReceive('reconnect')->andReturn(null);

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $service->reset($command);

        expect(file_exists($lockPath))->toBeFalse();
    });

    it('handles missing lock file gracefully', function (): void {
        $lockPath = storage_path('installed.lock');

        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        expect(file_exists($lockPath))->toBeFalse();

        $command = \Mockery::mock(Command::class);
        $command->shouldReceive('newLine')->atLeast()->once();

        \DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);
        \DB::shouldReceive('disconnect')->andReturn(null);
        \DB::shouldReceive('purge')->andReturn(null);
        \DB::shouldReceive('reconnect')->andReturn(null);

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $exitCode = $service->reset($command);

        expect($exitCode)->toBe(Command::SUCCESS);
    });

    it('returns success exit code on successful reset', function (): void {
        $lockPath = storage_path('installed.lock');
        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        $command = \Mockery::mock(Command::class);
        $command->shouldReceive('newLine')->atLeast()->once();

        \DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);
        \DB::shouldReceive('disconnect')->andReturn(null);
        \DB::shouldReceive('purge')->andReturn(null);
        \DB::shouldReceive('reconnect')->andReturn(null);

        Storage::shouldReceive('disk')->andReturnSelf();
        Storage::shouldReceive('deleteDirectory')->andReturn(true);

        $service = new ResetService;
        $exitCode = $service->reset($command);

        expect($exitCode)->toBe(Command::SUCCESS);
    });

    it('calls deleteDirectory on public and private disks', function (): void {
        $lockPath = storage_path('installed.lock');
        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        $command = \Mockery::mock(Command::class);
        $command->shouldReceive('newLine')->atLeast()->once();

        \DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);
        \DB::shouldReceive('disconnect')->andReturn(null);
        \DB::shouldReceive('purge')->andReturn(null);
        \DB::shouldReceive('reconnect')->andReturn(null);

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
        $service->reset($command);
    });

    it('returns failure exit code when exception occurs', function (): void {
        $lockPath = storage_path('installed.lock');
        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        $command = \Mockery::mock(Command::class);
        $command->shouldReceive('newLine')->atLeast()->once();

        \DB::shouldReceive('prohibitDestructiveCommands')->andReturn(null);

        Storage::shouldReceive('disk')->andThrow(new \RuntimeException('Storage failure'));

        $service = new ResetService;
        $exitCode = $service->reset($command);

        expect($exitCode)->toBe(Command::FAILURE);
    });
});
