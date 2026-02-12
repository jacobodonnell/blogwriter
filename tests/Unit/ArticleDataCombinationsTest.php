<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ==========================================
// Summary Field Tests
// ==========================================

it('creates article with summary', function (): void {
    $article = Article::factory()->create([
        'summary' => 'This is a custom summary for the article.',
    ]);

    expect($article->summary)->toBe('This is a custom summary for the article.');
    expect($article->summary)->not->toBeNull();
});

it('creates article without summary', function (): void {
    $article = Article::factory()->create([
        'summary' => null,
    ]);

    expect($article->summary)->toBeNull();
});

it('excerpt falls back to content when summary is null', function (): void {
    $article = Article::factory()->create([
        'summary' => null,
        'content' => 'This is the article content that should be used as excerpt when summary is null.',
    ]);

    expect($article->summary)->toBeNull();
    expect($article->excerpt)->toContain('This is the article content');
    expect(strlen((string) $article->excerpt))->toBeLessThanOrEqual(255);
});

it('excerpt uses summary when present', function (): void {
    $article = Article::factory()->create([
        'summary' => 'Custom summary text',
        'content' => 'Different content text',
    ]);

    expect($article->excerpt)->toBe('Custom summary text');
});

// ==========================================
// Featured Image Tests
// ==========================================

it('creates article without featured image', function (): void {
    $article = Article::factory()->create();

    // Factory has 60% chance of adding image, so we test explicit removal
    $article->clearMediaCollection('featured_image');

    expect($article->hasMedia('featured_image'))->toBeFalse();
    expect($article->featured_image_url)->toBeNull();
});

it('featured_image_url accessor returns null when no image attached', function (): void {
    $article = Article::factory()->create();
    $article->clearMediaCollection('featured_image');

    expect($article->featured_image_url)->toBeNull();
});

// ==========================================
// Meta Data Tests
// ==========================================

it('creates article with full meta data', function (): void {
    $article = Article::factory()->create([
        'meta' => [
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom meta description for SEO',
            'og_image' => 'https://example.com/og-image.jpg',
        ],
    ]);

    expect($article->meta)->toBeArray();
    expect($article->meta)->toHaveKey('meta_title');
    expect($article->meta)->toHaveKey('meta_description');
    expect($article->meta)->toHaveKey('og_image');
    expect($article->meta['meta_title'])->toBe('Custom Meta Title');
});

it('creates article without meta data', function (): void {
    $article = Article::factory()->create([
        'meta' => null,
    ]);

    expect($article->meta)->toBeNull();
});

it('meta_title accessor falls back to title when meta is null', function (): void {
    $article = Article::factory()->create([
        'title' => 'Article Title',
        'meta' => null,
    ]);

    expect($article->meta_title)->toBe('Article Title');
});

it('meta_description accessor falls back to excerpt when meta is null', function (): void {
    $article = Article::factory()->create([
        'summary' => 'This is the summary',
        'meta' => null,
    ]);

    expect($article->meta_description)->toBe('This is the summary');
});

it('creates article with partial meta data', function (): void {
    $article = Article::factory()->create([
        'meta' => [
            'meta_title' => 'Only Title Set',
        ],
    ]);

    expect($article->meta)->toHaveKey('meta_title');
    expect($article->meta['meta_title'])->toBe('Only Title Set');
});

// ==========================================
// Category Tests
// ==========================================

it('creates article without categories', function (): void {
    $article = Article::factory()->create();

    expect($article->categories()->count())->toBe(0);
});

it('creates article with single category', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);
    $article = Article::factory()->create();
    $article->categories()->attach($category);

    expect($article->categories()->count())->toBe(1);
    expect($article->categories->first()->name)->toBe('Technology');
});

it('creates article with multiple categories', function (): void {
    $tech = Category::factory()->create(['name' => 'Technology']);
    $satire = Category::factory()->create(['name' => 'Satire']);

    $article = Article::factory()->create();
    $article->categories()->attach([$tech->id, $satire->id]);

    expect($article->categories()->count())->toBe(2);
    expect($article->categories->pluck('name')->toArray())->toContain('Technology', 'Satire');
});

// ==========================================
// Status Tests
// ==========================================

it('creates published article with published status', function (): void {
    $article = Article::factory()->published()->create();

    expect($article->status)->toBe(Status::Published);
    expect($article->isPublished())->toBeTrue();
});

it('creates draft article with draft status', function (): void {
    $article = Article::factory()->draft()->create();

    expect($article->status)->toBe(Status::Draft);
    expect($article->isPublished())->toBeFalse();
});

// ==========================================
// Published At Tests
// ==========================================

it('published article has published_at set', function (): void {
    $article = Article::factory()->published()->create();

    expect($article->published_at)->not->toBeNull();
    expect($article->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

it('draft article has null published_at by default', function (): void {
    $article = Article::factory()->draft()->create();

    expect($article->published_at)->toBeNull();
});

it('can create draft with scheduled published_at', function (): void {
    $futureDate = now()->addDays(7);

    $article = Article::factory()->draft()->create([
        'published_at' => $futureDate,
    ]);

    expect($article->status)->toBe(Status::Draft);
    expect($article->published_at)->not->toBeNull();
    expect($article->published_at->isFuture())->toBeTrue();
});

// ==========================================
// Complex Combination Tests
// ==========================================

it('creates published article with all optional fields', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);

    $article = Article::factory()->published()->create([
        'summary' => 'Article summary',
        'meta' => [
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'og_image' => 'https://example.com/image.jpg',
        ],
    ]);

    $article->categories()->attach($category);

    expect($article->status)->toBe(Status::Published);
    expect($article->published_at)->not->toBeNull();
    expect($article->summary)->toBe('Article summary');
    expect($article->meta)->not->toBeNull();
    expect($article->categories()->count())->toBe(1);
});

it('creates draft article with minimal data', function (): void {
    $article = Article::factory()->draft()->create([
        'title' => 'Minimal Draft',
        'content' => 'Minimal content',
        'summary' => null,
        'meta' => null,
    ]);

    expect($article->status)->toBe(Status::Draft);
    expect($article->published_at)->toBeNull();
    expect($article->summary)->toBeNull();
    expect($article->meta)->toBeNull();
    expect($article->categories()->count())->toBe(0);
});

it('creates published article without summary but with meta', function (): void {
    $article = Article::factory()->published()->create([
        'summary' => null,
        'meta' => [
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom description',
        ],
    ]);

    expect($article->summary)->toBeNull();
    expect($article->meta)->not->toBeNull();
    expect($article->meta_title)->toBe('Custom Meta Title');
});

it('creates draft article with summary but without meta', function (): void {
    $article = Article::factory()->draft()->create([
        'summary' => 'Draft summary text',
        'meta' => null,
    ]);

    expect($article->summary)->toBe('Draft summary text');
    expect($article->meta)->toBeNull();
    expect($article->meta_description)->toBe('Draft summary text'); // Falls back to summary
});
