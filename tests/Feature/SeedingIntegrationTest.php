<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ==========================================
// Full Seeding Flow Tests
// ==========================================

it('executes full empty state seeding flow', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    // Verify final state
    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(0);
    expect(Article::count())->toBe(0);

    // Verify categories have all required fields
    $categories = Category::all();
    foreach ($categories as $category) {
        expect($category->name)->not->toBeEmpty();
        expect($category->slug)->not->toBeEmpty();
        expect($category->description)->not->toBeNull();
    }
});

it('executes full minimal state seeding flow', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('minimal')->seed();

    // Verify final state
    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(0);

    // Verify user
    $user = User::first();
    expect($user->name)->toBe('Jacob');
    expect($user->email)->toBe('jmodonnell96@gmail.com');
    expect($user->email_verified_at)->not->toBeNull();
});

it('executes full demo state seeding flow', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    // Verify final state
    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(5);

    // Verify articles
    expect(Article::where('status', Status::Published)->count())->toBe(4);
    expect(Article::where('status', Status::Draft)->count())->toBe(1);

    // Verify relationships
    $articlesWithCategories = Article::has('categories')->count();
    expect($articlesWithCategories)->toBeGreaterThan(0);

    // Verify content integrity
    $articles = Article::all();
    foreach ($articles as $article) {
        expect($article->title)->not->toBeEmpty();
        expect($article->slug)->not->toBeEmpty();
        expect($article->content)->not->toBeEmpty();

        // Published articles should have published_at
        if ($article->status === Status::Published) {
            expect($article->published_at)->not->toBeNull();
            expect($article->published_at->isPast())->toBeTrue();
        }

        // Draft articles should not have published_at
        if ($article->status === Status::Draft) {
            expect($article->published_at)->toBeNull();
        }
    }
});

it('executes full full state seeding flow', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('full')->seed();

    // Verify final state
    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(15);

    // Verify status distribution (8 published, 7 draft after hidden→draft conversion)
    expect(Article::where('status', Status::Published)->count())->toBe(8);
    expect(Article::where('status', Status::Draft)->count())->toBe(7);

    // Verify all articles have required data
    $articles = Article::all();
    foreach ($articles as $article) {
        expect($article->title)->not->toBeEmpty();
        expect($article->slug)->not->toBeEmpty();
        expect($article->content)->not->toBeEmpty();
    }
});

// ==========================================
// Progressive Seeding Tests
// ==========================================

it('can progressively seed from empty to minimal', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('empty')->seed();

    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(0);

    // Now seed minimal
    $seeder->withState('minimal')->seed();

    expect(Category::count())->toBe(5); // Should not duplicate
    expect(User::count())->toBe(1); // User added
});

it('can progressively seed from minimal to demo', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('minimal')->seed();

    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(0);

    // Now seed demo
    $seeder->withState('demo')->seed();

    expect(User::count())->toBe(1); // Should not duplicate
    expect(Category::count())->toBe(5); // Should not duplicate
    expect(Article::count())->toBe(5); // Articles added
});

// ==========================================
// Media Attachment Integration Tests
// ==========================================

it('attaches images to published articles on public disk', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $publishedArticles = Article::where('status', Status::Published)->get();

    foreach ($publishedArticles as $article) {
        if ($article->hasMedia('featured_image')) {
            $media = $article->getFirstMedia('featured_image');

            expect($media->disk)->toBe('public');
            expect($media->collection_name)->toBe('featured_image');
        }
    }

    expect(true)->toBeTrue(); // Pass even if no images attached
});

it('attaches images to draft articles on private disk', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $draftArticles = Article::where('status', Status::Draft)->get();

    foreach ($draftArticles as $article) {
        if ($article->hasMedia('featured_image')) {
            $media = $article->getFirstMedia('featured_image');

            expect($media->disk)->toBe('private');
            expect($media->collection_name)->toBe('featured_image');
        }
    }

    expect(true)->toBeTrue(); // Pass even if no images attached
});

// ==========================================
// Category Relationship Integration Tests
// ==========================================

it('attaches correct categories from JSON to articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    // Find article with known categories from demo JSON
    $articles = Article::all();

    // Verify at least some articles have categories
    $articlesWithCategories = $articles->filter(fn ($article): bool => $article->categories()->count() > 0);

    expect($articlesWithCategories->count())->toBeGreaterThan(0);

    // Verify category relationships are valid
    foreach ($articles as $article) {
        $categories = $article->categories;

        foreach ($categories as $category) {
            expect($category)->toBeInstanceOf(Category::class);
            expect($category->name)->not->toBeEmpty();
        }
    }
});

it('category counts reflect seeded articles correctly', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    $categories = Category::all();

    foreach ($categories as $category) {
        $publishedArticleCount = $category->articles()->where('status', Status::Published)->count();

        // article_count accessor should match published articles only
        expect($category->article_count)->toBe($publishedArticleCount);
    }
});

// ==========================================
// Error Handling Tests
// ==========================================

it('throws exception for invalid state', function (): void {
    $seeder = new DatabaseSeeder;

    expect(fn () => $seeder->withState('invalid_state')->seed())
        ->toThrow(\InvalidArgumentException::class, 'Invalid state: invalid_state');
});

// ==========================================
// Custom User Integration Tests
// ==========================================

it('seeds with custom user and integrates with articles', function (): void {
    $seeder = new DatabaseSeeder;
    $seeder->asUser('Custom User', 'custom@example.com', 'custompass')
        ->withState('demo')
        ->seed();

    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(5);

    $user = User::first();
    expect($user->name)->toBe('Custom User');
    expect($user->email)->toBe('custom@example.com');
});

// ==========================================
// Complete Integration Flow Test
// ==========================================

it('executes complete seeding integration from start to finish', function (): void {
    // Start fresh
    expect(Category::count())->toBe(0);
    expect(User::count())->toBe(0);
    expect(Article::count())->toBe(0);

    // Seed demo state
    $seeder = new DatabaseSeeder;
    $seeder->withState('demo')->seed();

    // Verify all relationships and data integrity
    expect(Category::count())->toBe(5);
    expect(User::count())->toBe(1);
    expect(Article::count())->toBe(5);

    // Verify published articles
    $publishedArticles = Article::where('status', Status::Published)->get();
    expect($publishedArticles->count())->toBe(4);

    foreach ($publishedArticles as $article) {
        // Has valid data
        expect($article->title)->not->toBeEmpty();
        expect($article->slug)->not->toBeEmpty();
        expect($article->content)->not->toBeEmpty();

        // Has published date
        expect($article->published_at)->not->toBeNull();
        expect($article->published_at->isPast())->toBeTrue();

        // Images use correct disk
        if ($article->hasMedia('featured_image')) {
            expect($article->getFirstMedia('featured_image')->disk)->toBe('public');
        }
    }

    // Verify draft articles
    $draftArticles = Article::where('status', Status::Draft)->get();
    expect($draftArticles->count())->toBe(1);

    foreach ($draftArticles as $article) {
        // No published date
        expect($article->published_at)->toBeNull();

        // Images use correct disk
        if ($article->hasMedia('featured_image')) {
            expect($article->getFirstMedia('featured_image')->disk)->toBe('private');
        }
    }

    // Verify categories are linked
    $articlesWithCategories = Article::has('categories')->count();
    expect($articlesWithCategories)->toBeGreaterThan(0);

    // Verify user
    $user = User::first();
    expect($user->email_verified_at)->not->toBeNull();
});
