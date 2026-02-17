<?php

namespace App\Console\Commands;

use App\Services\ResetService;
use Illuminate\Console\Command;

class UninstallCommand extends Command
{
    protected $signature = 'blogwriter:uninstall {--force : Skip confirmation}';

    protected $description = 'Uninstall BlogWriter and reset to a clean state';

    public function __construct(public ResetService $resetService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will DELETE all content, users, and uploaded files. Are you sure?', false)) {
            $this->info('Uninstall cancelled.');

            return self::SUCCESS;
        }

        $result = $this->resetService->reset($this);

        if ($result === self::SUCCESS) {
            $this->newLine();
            $this->info('BlogWriter has been uninstalled. Run `php artisan blogwriter:install` to set up again.');
        }

        return $result;
    }
}
