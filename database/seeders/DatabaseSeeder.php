<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

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
     * Path to test data files
     */
    protected string $testDataPath = 'database/seeders/test-data/';

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
            throw new \InvalidArgumentException("Invalid state: {$state}. Valid states: ".implode(', ', $this->validStates));
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
        $this->command?->info("Seeding state: {$this->currentState}");
        $this->command?->info("User: {$this->userConfig['name']} ({$this->userConfig['email']})");

        $this->seedState($this->currentState);

        $this->command?->info('Seeding completed successfully!');
    }

    /**
     * Seed specific state
     */
    protected function seedState(string $state): void
    {
        // Always seed categories first (shared across all states)
        $this->seedCategories();

        switch ($state) {
            case 'empty':
                // Categories only - no user or articles
                break;

            case 'minimal':
                $this->seedUser();
                break;

            case 'demo':
                $this->seedUser();
                $this->seedArticles('demo');
                break;

            case 'full':
                $this->seedUser();
                $this->seedArticles('full');
                break;
        }
    }

    /**
     * Seed categories from JSON
     */
    protected function seedCategories(): void
    {
        $this->command?->info('Seeding categories...');

        $categoriesFile = base_path($this->testDataPath.'categories.json');

        if (! File::exists($categoriesFile)) {
            throw new \RuntimeException("Categories file not found: {$categoriesFile}");
        }

        $categories = json_decode(File::get($categoriesFile), true);

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        $this->command?->info('Categories seeded: '.count($categories));
    }

    /**
     * Seed single user with configured credentials
     */
    protected function seedUser(): void
    {
        $this->command?->info('Seeding user...');

        $user = User::firstOrCreate(
            ['email' => $this->userConfig['email']],
            [
                'name' => $this->userConfig['name'],
                'email' => $this->userConfig['email'],
                'password' => Hash::make($this->userConfig['password']),
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info("User created: {$user->name} ({$user->email})");
    }

    /**
     * Seed articles from JSON with status distribution
     */
    protected function seedArticles(string $state): void
    {
        $this->command?->info("Seeding articles from {$state} state...");

        $articlesFile = base_path($this->testDataPath.$state.'/articles.json');

        if (! File::exists($articlesFile)) {
            throw new \RuntimeException("Articles file not found: {$articlesFile}");
        }

        $articles = json_decode(File::get($articlesFile), true);
        $totalCount = count($articles);

        // Status distribution: 70% published, 20% draft, 10% hidden
        $publishedCount = (int) round($totalCount * 0.7);
        $draftCount = (int) round($totalCount * 0.2);
        // Remainder go to hidden

        // Shuffle articles for random status assignment
        shuffle($articles);

        foreach ($articles as $index => $articleData) {
            // Determine status based on distribution
            if ($index < $publishedCount) {
                $status = Status::Published;
            } elseif ($index < $publishedCount + $draftCount) {
                $status = Status::Draft;
            } else {
                $status = Status::Hidden;
            }

            // Create article
            $article = Article::create([
                'title' => $articleData['title'],
                'slug' => $articleData['slug'],
                'content' => $articleData['content'],
                'summary' => $articleData['summary'] ?? substr(strip_tags($articleData['content']), 0, 255),
                'status' => $status,
                'published_at' => $status === Status::Published ? now()->subDays(rand(1, 30)) : null,
                'meta' => $articleData['meta'] ?? null,
            ]);

            // Add featured image via Spatie Media Library
            if (!empty($articleData['featured_image'])) {
                // Use local demo images instead of external URLs
                $demoImagesPath = database_path('seeders/demo-images');
                $demoImages = [
                    'demo-image-1.png',
                    'demo-image-2.png',
                    'demo-image-3.png',
                    'demo-image-4.png',
                    'demo-image-5.png',
                ];

                $randomImage = $demoImages[array_rand($demoImages)];
                $imagePath = $demoImagesPath . '/' . $randomImage;

                if (file_exists($imagePath)) {
                    // Determine disk based on status
                    $disk = $status === Status::Published ? 'public' : 'private';

                    $article->addMedia($imagePath)
                        ->preservingOriginal() // Don't delete source file
                        ->toMediaCollection('featured_image', $disk);
                }
            }

            // Attach categories
            if (! empty($articleData['categories'])) {
                $categoryIds = Category::whereIn('name', $articleData['categories'])->pluck('id');
                $article->categories()->attach($categoryIds);
            }
        }

        $this->command?->info("Articles seeded: {$totalCount}");
        $this->command?->info("- Published: {$publishedCount}");
        $this->command?->info("- Draft: {$draftCount}");
        $this->command?->info('- Hidden: '.($totalCount - $publishedCount - $draftCount));
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
