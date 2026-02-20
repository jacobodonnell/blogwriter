<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use InvalidArgumentException;

final class SeedCommand extends Command
{
    protected $signature = 'blogwriter:seed
                            {--state=demo : The seeding state (empty, minimal, demo, full)}
                            {--user= : User config in format "Name,email,password"}
                            {--clear : Clear all data before seeding}';

    protected $description = 'Seed the database with BlogWriter test data';

    public function handle(): int
    {
        $state = $this->option('state');
        $user = $this->option('user');
        $clear = $this->option('clear');

        $this->info('🌱 BlogWriter Database Seeder');
        $this->info('State: '.$state);

        if ($clear) {
            $this->warn('Clearing all data...');
            \App\Models\Article::query()->delete();
            \App\Models\Category::query()->delete();
            \App\Models\User::query()->delete();
            $this->info('Data cleared!');
        }

        // Create seeder instance and configure it
        $seeder = new DatabaseSeeder;
        $seeder->setContainer($this->laravel);
        $seeder->setCommand($this);

        // Configure user if provided
        if ($user) {
            $parts = explode(',', $user);
            if (count($parts) === 3) {
                $seeder->asUser(mb_trim($parts[0]), mb_trim($parts[1]), mb_trim($parts[2]));
            } else {
                $this->warn('User option format invalid. Expected: "Name,email,password"');
            }
        }

        // Set state
        try {
            $seeder->withState($state);
        } catch (InvalidArgumentException) {
            $this->error('Invalid state: '.$state);
            $this->error('Valid states: empty, minimal, demo, full');

            return 1;
        }

        // Run seeder
        $seeder->seed();

        $this->info('✅ Seeding completed successfully!');

        // Show stats
        $user = \App\Models\User::first();
        if ($user) {
            $this->info('
👤 User: '.$user->email);
        }

        $published = \App\Models\Article::published()->count();
        $draft = \App\Models\Article::draft()->count();

        if ($published + $draft > 0) {
            $this->info("\n📝 Articles: {$published} published, {$draft} draft");
        }

        $this->info('🏷️ Categories: '.\App\Models\Category::count());

        return 0;
    }
}
