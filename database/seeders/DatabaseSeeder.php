<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * User configuration for single-user setup
     */
    protected array $userConfig = [
        'name' => 'Jacob',
        'email' => 'jmodonnell96@gmail.com',
        'password' => 'password',
    ];

    /**
     * Current state to seed
     */
    protected string $currentState = 'demo';

    /**
     * Available states
     */
    protected array $validStates = ['empty', 'minimal', 'demo', 'full'];

    /**
     * Main entry point - called by artisan db:seed
     */
    public function run(): void
    {
        // Check for command line options (only available via blogwriter:seed command)
        if ($this->command && method_exists($this->command, 'hasOption')) {
            if ($this->command->hasOption('user') && $this->command->option('user')) {
                $this->parseUserOption($this->command->option('user'));
            }

            if ($this->command->hasOption('state') && $this->command->option('state')) {
                $this->withState($this->command->option('state'));
            }
        }

        $this->seed();
    }

    /**
     * Fluent method to configure user
     */
    public function asUser(string $name, string $email, string $password): self
    {
        $this->userConfig = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];

        return $this;
    }

    /**
     * Fluent method to set initial state
     */
    public function withState(string $state): self
    {
        if (! in_array($state, $this->validStates)) {
            throw new \InvalidArgumentException(sprintf('Invalid state: %s. Valid states: ', $state).implode(', ', $this->validStates));
        }

        $this->currentState = $state;

        return $this;
    }

    /**
     * Chain additional state (for progressive seeding)
     */
    public function then(string $state): self
    {
        // Execute current state first
        $this->seedState($this->currentState);

        // Set next state
        $this->currentState = $state;

        return $this;
    }

    /**
     * Execute seeding
     */
    public function seed(): void
    {
        $this->command?->info('Seeding state: '.$this->currentState);
        $this->command?->info(sprintf('User: %s (%s)', $this->userConfig['name'], $this->userConfig['email']));

        $this->seedState($this->currentState);

        $this->command?->info('Seeding completed successfully!');
    }

    /**
     * Seed specific state
     */
    protected function seedState(string $state): void
    {
        // Always seed categories first (shared across all states)
        $this->call(CategorySeeder::class);

        switch ($state) {
            case 'empty':
                // Categories only - no user or articles
                break;

            case 'minimal':
                $this->seedUser();
                break;

            case 'demo':
                $this->seedUser();
                $this->command?->info('Creating demo photos (image processing may take a moment)...');
                $this->call(PhotoSeeder::class);
                $this->command?->info('Creating demo articles...');
                $this->call(DemoArticleSeeder::class);
                break;

            case 'full':
                $this->seedUser();
                $this->call(FullArticleSeeder::class);
                break;
        }
    }

    /**
     * Seed single user with configured credentials
     */
    protected function seedUser(): void
    {
        $this->command?->info('Seeding user...');

        $user = User::first() ?? User::create([
            'name' => $this->userConfig['name'],
            'email' => $this->userConfig['email'],
            'password' => $this->userConfig['password'],
            'email_verified_at' => now(),
        ]);

        $this->command?->info(sprintf('User created: %s (%s)', $user->name, $user->email));
    }

    /**
     * Parse user option from command line
     * Format: --user="Name,email,password"
     */
    protected function parseUserOption(string $userOption): void
    {
        $parts = explode(',', $userOption);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('User option must be in format: "Name,email,password"');
        }

        $this->asUser(trim($parts[0]), trim($parts[1]), trim($parts[2]));
    }

    /**
     * Clear all seeded data
     */
    public function clear(): self
    {
        $this->command?->info('Clearing seeded data...');

        Article::query()->delete();
        Category::query()->delete();
        User::query()->delete();

        $this->command?->info('All data cleared!');

        return $this;
    }
}
