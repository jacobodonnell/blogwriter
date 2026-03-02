<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonInterval;
use Illuminate\Console\Command;

final class DemoStatusCommand extends Command
{
    protected $signature = 'demo:status';

    protected $description = 'Show demo mode status and time until next reset';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->warn('Demo mode is not enabled.');

            return self::SUCCESS;
        }

        $interval = (int) config('demo.reset_interval', 30);

        $this->info('Demo mode is active.');
        $this->newLine();

        $this->table(['Setting', 'Value'], [
            ['Email', config('demo.credentials.email')],
            ['Password', config('demo.credentials.password')],
            ['Reset Interval', $interval.' minutes'],
        ]);

        $lockFile = storage_path('installed.lock');

        if (! file_exists($lockFile)) {
            $this->newLine();
            $this->warn('No reset has been run yet. Run: php artisan demo:reset');

            return self::SUCCESS;
        }

        $lastReset = filemtime($lockFile);
        $elapsed = time() - $lastReset;
        $intervalSeconds = $interval * 60;
        $remaining = max(0, $intervalSeconds - $elapsed);

        $this->newLine();
        $this->info('Last reset: '.date('Y-m-d H:i:s', $lastReset));

        if ($remaining === 0) {
            $this->warn('Next reset is overdue (scheduler may not be running).');
        } else {
            $this->info('Next reset in: '.CarbonInterval::seconds($remaining)->cascade()->forHumans(['short' => true]));
        }

        return self::SUCCESS;
    }
}
