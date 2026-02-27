<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Resettable;
use App\Models\Setting;
use App\Services\InstallService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset the demo instance to a clean state';

    public function __construct(
        private Resettable $resetService,
        private InstallService $installService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->error('Demo mode is not enabled. Set DEMO_MODE=true in your .env file.');

            return self::FAILURE;
        }

        DB::prohibitDestructiveCommands(false);

        try {
            return $this->reset();
        } finally {
            DB::prohibitDestructiveCommands(true);
        }
    }

    private function reset(): int
    {
        $this->info('Resetting demo instance...');

        $exitCode = $this->resetService->reset($this);

        if ($exitCode !== self::SUCCESS) {
            $this->error('Reset failed.');

            return self::FAILURE;
        }

        $this->installService->ensureDatabaseFile();
        $this->installService->runMigrations();
        $this->info('✓ Database migrated');

        $this->installService->createUser([
            'name' => 'Demo User',
            'email' => config('demo.credentials.email'),
            'password' => config('demo.credentials.password'),
        ]);
        $this->info('✓ Demo user created');

        Artisan::call('blogwriter:seed', ['--state' => 'demo', '--no-interaction' => true], $this->output);
        $this->info('✓ Demo content seeded');

        $this->seedPlaceholderImage();

        $this->installService->createLockFile();
        $this->info('✓ Installation lock created');

        $this->installService->clearCaches();
        $this->info('✓ Caches cleared');

        $this->newLine();
        $this->info('Demo instance reset complete!');

        return self::SUCCESS;
    }

    private function seedPlaceholderImage(): void
    {
        $source = database_path('seeders/blogwriter-placeholder.jpg');
        $destination = 'blogwriter/blogwriter-placeholder.jpg';

        if (file_exists($source) && ! Storage::disk('public')->exists($destination)) {
            Storage::disk('public')->makeDirectory('blogwriter');
            Storage::disk('public')->put($destination, file_get_contents($source));
        }

        if (! Setting::get('site_placeholder_image') && Storage::disk('public')->exists($destination)) {
            Setting::set('site_placeholder_image', $destination);
        }
    }
}
