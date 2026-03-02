<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\CarbonInterval;
use Cron\CronExpression;
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

        $cron = new CronExpression("*/{$interval} * * * *");
        $nextReset = $cron->getNextRunDate();
        $remaining = $nextReset->getTimestamp() - time();

        $this->newLine();
        $this->info('Next reset at: '.$nextReset->format('Y-m-d H:i:s'));
        $this->info('Next reset in: '.CarbonInterval::seconds($remaining)->cascade()->forHumans(['short' => true]));

        return self::SUCCESS;
    }
}
