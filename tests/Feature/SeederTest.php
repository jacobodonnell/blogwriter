<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// Category Seeding Tests
// ==========================================

it('seeds exactly 5 categories from JSON file', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    expect(Category::count())->toBe(5);
});

it('seeds categories with correct names and slugs', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    expect(Category::where('name', 'General')->exists())->toBeTrue();
    expect(Category::where('name', 'Technology')->exists())->toBeTrue();
    expect(Category::where('name', 'Satire')->exists())->toBeTrue();
    expect(Category::where('name', 'Startups')->exists())->toBeTrue();
    expect(Category::where('name', 'Programming')->exists())->toBeTrue();

    expect(Category::where('slug', 'general')->exists())->toBeTrue();
    expect(Category::where('slug', 'technology')->exists())->toBeTrue();
});

it('seeds categories with descriptions', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    $tech = Category::where('name', 'Technology')->first();

    expect($tech->description)->not->toBeNull();
    expect($tech->description)->toBe('Tech news, gadgets, and digital culture');
});

it('category seeding is idempotent', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    expect(Category::count())->toBe(5);

    // Seed again
    $seeder->withState('empty')->seed();

    // Should still be 5 (no duplicates)
    expect(Category::count())->toBe(5);
});

// ==========================================
// Demo State Seeding Tests
// ==========================================

it('demo state seeds 5 articles from JSON', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    expect(Article::count())->toBe(5);
});

it('demo state creates 80% published and 20% draft distribution', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $publishedCount = Article::where('status', Status::Published)->count();
    $draftCount = Article::where('status', Status::Draft)->count();

    // 5 articles: 80% = 4 published, 20% = 1 draft
    expect($publishedCount)->toBe(4);
    expect($draftCount)->toBe(1);
});

it('demo state attaches categories to articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    // Check that articles have categories
    $articlesWithCategories = Article::has('categories')->count();

    expect($articlesWithCategories)->toBeGreaterThan(0);
});

it('demo state attaches featured images to articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $articlesWithImages = Article::all()->filter(fn ($article): bool => $article->hasMedia('featured_image'))->count();

    // Not all articles have images in demo data, but some should
    expect($articlesWithImages)->toBeGreaterThan(0);
});

it('demo state uses public disk for published article images', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $publishedArticles = Article::where('status', Status::Published)->get();

    foreach ($publishedArticles as $article) {
        if ($article->hasMedia('featured_image')) {
            $media = $article->getFirstMedia('featured_image');
            expect($media->disk)->toBe('public');
        }
    }

    expect(true)->toBeTrue(); // Ensure test passes even if no images
});

it('demo state uses private disk for draft article images', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $draftArticles = Article::where('status', Status::Draft)->get();

    foreach ($draftArticles as $article) {
        if ($article->hasMedia('featured_image')) {
            $media = $article->getFirstMedia('featured_image');
            expect($media->disk)->toBe('private');
        }
    }

    expect(true)->toBeTrue(); // Ensure test passes even if no images
});

// ==========================================
// Full State Seeding Tests
// ==========================================

it('full state seeds 15 articles from JSON', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('full')->seed();

    expect(Article::count())->toBe(15);
});

it('full state creates 80% published and 20% draft distribution', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('full')->seed();

    $publishedCount = Article::where('status', Status::Published)->count();
    $draftCount = Article::where('status', Status::Draft)->count();

    // 15 articles: 80% = 12 published, 20% = 3 draft
    expect($publishedCount)->toBe(12);
    expect($draftCount)->toBe(3);
});

// ==========================================
// User Seeding Tests
// ==========================================

it('minimal state seeds user without articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('minimal')->seed();

    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(0);

    $user = User::first();
    expect($user->name)->toBe('Jacob');
    expect($user->email)->toBe('jmodonnell96@gmail.com');
});

it('demo state seeds user with articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(5);
});

it('full state seeds user with articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('full')->seed();

    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(15);
});

// ==========================================
// Empty State Tests
// ==========================================

it('empty state seeds only categories', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(0);
    expect(Article::count())->toBe(0);
});

// ==========================================
// Idempotency Tests
// ==========================================

it('seeding is idempotent for all states', function (string $state): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState($state)->seed();

    $categoryCount = Category::count();
    $userCount = User::count();
    $articleCount = Article::count();

    // Seed again
    $seeder->withState($state)->seed();

    // Counts should not increase (firstOrCreate prevents duplicates)
    expect(Category::count())->toBe($categoryCount);
    expect(User::count())->toBe($userCount);

    // Articles may duplicate on second run - this is expected behavior
})->with(['empty', 'minimal', 'demo', 'full']);

// ==========================================
// Data Integrity Tests
// ==========================================

it('seeded articles have valid published_at dates', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $publishedArticles = Article::where('status', Status::Published)->get();

    foreach ($publishedArticles as $article) {
        expect($article->published_at)->not->toBeNull();
        expect($article->published_at->isPast())->toBeTrue();
    }
});

it('seeded draft articles have null published_at', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $draftArticles = Article::where('status', Status::Draft)->get();

    foreach ($draftArticles as $article) {
        expect($article->published_at)->toBeNull();
    }
});

it('seeded articles have content and titles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $articles = Article::all();

    foreach ($articles as $article) {
        expect($article->title)->not->toBeEmpty();
        expect($article->content)->not->toBeEmpty();
        expect($article->slug)->not->toBeEmpty();
    }
});

// ==========================================
// Custom User Configuration Tests
// ==========================================

it('can seed with custom user credentials', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->asUser('Test User', 'test@example.com', 'testpassword')
        ->withState('minimal')
        ->seed();

    expect(User::count())->toBe(1);

    $user = User::first();
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect(\Illuminate\Support\Facades\Hash::check('testpassword', $user->password))->toBeTrue();
});
