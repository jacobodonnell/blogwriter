<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Resettable;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

final class UninstallCommand extends Command
{
    protected $signature = 'blogwriter:uninstall {--force : Skip confirmation}';

    protected $description = 'Uninstall BlogWriter and reset to a clean state';

    public function __construct(public Resettable $resetService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->option('force') && ! confirm('This will DELETE all content, users, and uploaded files. Are you sure?', default: false)) {
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
