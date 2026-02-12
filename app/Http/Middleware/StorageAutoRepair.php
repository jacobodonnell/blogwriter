<?php

namespace App\Http\Middleware;

use App\Models\SystemEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StorageAutoRepair
{
    /**
     * Required storage directories for Laravel to function properly.
     */
    protected array $requiredDirectories = [
        'storage/framework/views',
        'storage/framework/sessions',
        'storage/framework/cache',
        'storage/framework/cache/data',
        'storage/logs',
        'bootstrap/cache',
    ];

    /**
     * Handle an incoming request.
     *
     * Only runs repair logic for authenticated users to minimize
     * performance impact on public frontend requests.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only auto-repair for authenticated users
        if (Auth::check()) {
            $this->repairIfNeeded();
        }

        return $next($request);
    }

    /**
     * Check and repair storage directories if needed.
     */
    protected function repairIfNeeded(): void
    {
        $repaired = [];
        $failed = [];

        foreach ($this->requiredDirectories as $dir) {
            $path = base_path($dir);

            if (! is_dir($path)) {
                try {
                    if (! mkdir($path, 0755, true)) {
                        $failed[] = ['path' => $dir, 'reason' => 'mkdir_failed'];

                        continue;
                    }

                    $repaired[] = $dir;
                } catch (\Exception $e) {
                    $failed[] = ['path' => $dir, 'reason' => $e->getMessage()];
                }
            } elseif (! is_writable($path)) {
                $failed[] = ['path' => $dir, 'reason' => 'not_writable'];
            }
        }

        // Log successful repairs
        if ($repaired !== []) {
            $this->logRepair($repaired);
        }

        // Log failures as errors
        if ($failed !== []) {
            $this->logFailures($failed);
        }
    }

    /**
     * Log successful storage repairs to system events.
     */
    protected function logRepair(array $repaired): void
    {
        $message = 'Storage directories auto-created: '.implode(', ', $repaired);

        // Log to Laravel log for immediate visibility
        Log::info($message, ['directories' => $repaired]);

        // Store in system_events table for future GUI alerts
        try {
            SystemEvent::create([
                'type' => 'storage_repair',
                'message' => $message,
                'context' => ['directories' => $repaired],
                'severity' => 'info',
                'is_error_log' => false,
                'resolved' => true, // Auto-resolved since we just fixed it
            ]);
        } catch (\Exception $exception) {
            // If database isn't available yet, just log to file
            Log::warning('Could not log storage repair to database', ['error' => $exception->getMessage()]);
        }
    }

    /**
     * Log failed storage repairs as errors.
     */
    protected function logFailures(array $failed): void
    {
        $paths = array_column($failed, 'path');
        $message = 'Storage directory creation failed for: '.implode(', ', $paths);

        // Log to Laravel log as warning
        Log::warning($message, ['failures' => $failed]);

        // Store in system_events table as error log
        try {
            SystemEvent::create([
                'type' => 'storage_repair_failed',
                'message' => $message,
                'context' => ['failures' => $failed],
                'severity' => 'error',
                'is_error_log' => true,
                'resolved' => false,
            ]);
        } catch (\Exception $exception) {
            // If database isn't available, log to file
            Log::error('Could not log storage repair failure to database', ['error' => $exception->getMessage()]);
        }
    }
}
