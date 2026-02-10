<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blogwriter:seed
                            {--state=demo : The seeding state (empty, minimal, demo, full)}
                            {--user= : User config in format "Name,email,password"}
                            {--clear : Clear all data before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with BlogWriter test data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $state = $this->option('state');
        $user = $this->option('user');
        $clear = $this->option('clear');

        $this->info('🌱 BlogWriter Database Seeder');
        $this->info("State: {$state}");

        if ($clear) {
            $this->warn('Clearing all data...');
            \App\Models\Article::query()->delete();
            \App\Models\Category::query()->delete();
            \App\Models\User::query()->delete();
            $this->info('Data cleared!');
        }

        // Build the command
        $options = [
            '--class' => 'DatabaseSeeder',
            '--state' => $state,
        ];

        if ($user) {
            $options['--user'] = $user;
        }

        // Call the seeder
        $exitCode = Artisan::call('db:seed', $options);

        if ($exitCode === 0) {
            $this->info('✅ Seeding completed successfully!');

            // Show user info
            $user = \App\Models\User::first();
            if ($user) {
                $this->info("\n👤 User Credentials:");
                $this->info("   Email: {$user->email}");
                $this->info('   Password: (as configured)');
            }

            // Show article stats
            $published = \App\Models\Article::published()->count();
            $draft = \App\Models\Article::draft()->count();
            $hidden = \App\Models\Article::hidden()->count();

            if ($published + $draft + $hidden > 0) {
                $this->info("\n📝 Articles:");
                $this->info("   Published: {$published}");
                $this->info("   Draft: {$draft}");
                $this->info("   Hidden: {$hidden}");
            }

            // Show categories
            $categories = \App\Models\Category::count();
            $this->info("\n🏷️ Categories: {$categories}");

            return 0;
        }

        $this->error('❌ Seeding failed!');

        return 1;
    }
}
