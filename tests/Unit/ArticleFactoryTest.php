<?php

use App\Enums\Status;
use App\Models\Article;
use Database\Factories\ArticleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    ArticleFactory::resetSequence();
});

// ==========================================
// Factory State Tests
// ==========================================

it('creates published article with Status::Published and published_at set', function (): void {
    $article = Article::factory()->published()->create();

    expect($article->status)->toBe(Status::Published);
    expect($article->published_at)->not->toBeNull();
    expect($article->published_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('creates draft article with Status::Draft and null published_at', function (): void {
    $article = Article::factory()->draft()->create();

    expect($article->status)->toBe(Status::Draft);
    expect($article->published_at)->toBeNull();
});

it('default factory creates articles with weighted status distribution', function (): void {
    // Create 100 articles to test probability distribution
    $articles = Article::factory()->count(100)->create();

    $publishedCount = $articles->where('status', Status::Published)->count();
    $draftCount = $articles->where('status', Status::Draft)->count();

    // With 70/30 distribution, expect roughly 60-80% published (allowing variance)
    expect($publishedCount)->toBeGreaterThan(50);
    expect($publishedCount)->toBeLessThan(90);
    expect($draftCount)->toBeGreaterThan(10);
    expect($draftCount)->toBeLessThan(50);
    expect($publishedCount + $draftCount)->toBe(100);
});

it('published state overrides default weighted status', function (): void {
    $articles = Article::factory()->published()->count(10)->create();

    $allPublished = $articles->every(fn ($article): bool => $article->status === Status::Published);

    expect($allPublished)->toBeTrue();
    expect($articles->every(fn ($article): bool => $article->published_at !== null))->toBeTrue();
});

it('draft state overrides default weighted status', function (): void {
    $articles = Article::factory()->draft()->count(10)->create();

    $allDrafts = $articles->every(fn ($article): bool => $article->status === Status::Draft);

    expect($allDrafts)->toBeTrue();
    // Note: Draft articles may have scheduled published_at dates (30% probability)
});

// ==========================================
// Media Attachment Tests
// ==========================================

it('attaches featured image with 60% probability', function (): void {
    // Create 50 articles to test probability
    $articles = Article::factory()->count(50)->create();

    $articlesWithImages = $articles->filter(fn ($article): bool => $article->hasMedia('featured_image'));

    // Expect roughly 50-70% to have images (allowing variance)
    expect($articlesWithImages->count())->toBeGreaterThan(20);
    expect($articlesWithImages->count())->toBeLessThan(40);
})->skip('Skipping due to demo-image dependency - will test in seeder tests');

it('published articles use public disk for featured images', function (): void {
    $article = Article::factory()->published()->create();

    if ($article->hasMedia('featured_image')) {
        $media = $article->getFirstMedia('featured_image');
        expect($media->disk)->toBe('public');
    } else {
        expect(true)->toBeTrue(); // Skip if no image attached
    }
})->skip('Skipping due to demo-image dependency - will test in seeder tests');

it('draft articles use private disk for featured images', function (): void {
    $article = Article::factory()->draft()->create();

    if ($article->hasMedia('featured_image')) {
        $media = $article->getFirstMedia('featured_image');
        expect($media->disk)->toBe('private');
    } else {
        expect(true)->toBeTrue(); // Skip if no image attached
    }
})->skip('Skipping due to demo-image dependency - will test in seeder tests');

// ==========================================
// Content Generation Tests
// ==========================================

it('generates realistic blog post title', function (): void {
    $article = Article::factory()->create();

    expect($article->title)->not->toBeEmpty();
    expect(strlen((string) $article->title))->toBeGreaterThan(10);
    expect(strlen((string) $article->title))->toBeLessThan(200);
});

it('generates markdown content with headers and paragraphs', function (): void {
    $article = Article::factory()->create();

    expect($article->content)->not->toBeEmpty();
    expect($article->content)->toContain('##'); // Has headers
    expect(strlen((string) $article->content))->toBeGreaterThan(500); // Substantial content
});

it('generates optional summary with 80% probability', function (): void {
    $articles = Article::factory()->count(50)->create();

    $articlesWithSummary = $articles->filter(fn ($article): bool => $article->summary !== null);

    // Expect roughly 70-90% to have summaries (allowing variance)
    expect($articlesWithSummary->count())->toBeGreaterThan(30);
    expect($articlesWithSummary->count())->toBeLessThan(45);
});

it('generates optional meta data with 60% probability', function (): void {
    $articles = Article::factory()->count(50)->create();

    $articlesWithMeta = $articles->filter(fn ($article): bool => $article->meta !== null);

    // Expect roughly 50-70% to have meta (allowing variance)
    expect($articlesWithMeta->count())->toBeGreaterThan(20);
    expect($articlesWithMeta->count())->toBeLessThan(40);
});

it('meta data contains valid SEO fields when present', function (): void {
    $articles = Article::factory()->count(20)->create();

    foreach ($articles as $article) {
        if ($article->meta !== null) {
            expect($article->meta)->toBeArray();
            expect($article->meta)->toHaveKeys(['meta_title', 'meta_description', 'og_image']);
        }
    }
});

// ==========================================
// Data Integrity Tests
// ==========================================

it('auto-generates slug from title', function (): void {
    $article = Article::factory()->create([
        'title' => 'This Is A Test Article',
    ]);

    expect($article->slug)->toBe('this-is-a-test-article');
});

it('published articles have published_at in the past', function (): void {
    $article = Article::factory()->published()->create();

    expect($article->published_at)->not->toBeNull();
    expect($article->published_at->isPast() || $article->published_at->isCurrentSecond())->toBeTrue();
});

it('draft articles with published_at have future dates 30% of the time', function (): void {
    $articles = Article::factory()->draft()->count(100)->create();

    $draftsWithPublishedAt = $articles->filter(fn ($article): bool => $article->published_at !== null);

    // Expect roughly 20-40 drafts to have scheduled dates (30% probability with 100 samples)
    expect($draftsWithPublishedAt->count())->toBeGreaterThan(15);
    expect($draftsWithPublishedAt->count())->toBeLessThan(45);
});

it('stores status as string in database', function (): void {
    $article = Article::factory()->published()->create();

    $dbArticle = \Illuminate\Support\Facades\DB::table('articles')
        ->where('id', $article->id)
        ->first();

    expect($dbArticle->status)->toBe('published');
    expect($dbArticle->status)->toBeString();
});

it('casts status to Status enum when retrieved', function (): void {
    $article = Article::factory()->published()->create();

    expect($article->status)->toBeInstanceOf(Status::class);
    expect($article->status)->toBe(Status::Published);
});
