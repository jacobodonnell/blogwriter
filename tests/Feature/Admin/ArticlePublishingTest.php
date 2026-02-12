<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

// ==========================================
// Happy Path Tests
// ==========================================

it('creates article with published status and auto-sets published_at', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Test Published Article',
        'slug' => 'test-published-article',
        'content' => 'This is the article content.',
        'status' => 'published',
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'test-published-article')->first();
    expect($article)->not->toBeNull();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->not->toBeNull();
    expect($article->published_at->diffInSeconds(now()))->toBeLessThan(5);
    expect($article->last_edited_at)->toBeNull();
});

it('creates article with draft status and keeps published_at null', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Test Draft Article',
        'slug' => 'test-draft-article',
        'content' => 'This is the draft content.',
        'status' => 'draft',
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'test-draft-article')->first();
    expect($article)->not->toBeNull();
    expect($article->status->value)->toBe('draft');
    expect($article->published_at)->toBeNull();
    expect($article->last_edited_at)->toBeNull();
});

it('updates draft to published and auto-sets published_at', function (): void {
    $article = Article::factory()->draft()->create([
        'title' => 'Draft Article',
        'slug' => 'draft-to-published',
        'content' => 'Original content.',
    ]);

    expect($article->published_at)->toBeNull();

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Draft Article',
        'slug' => 'draft-to-published',
        'content' => 'Updated content.',
        'status' => 'published',
    ]);

    $response->assertRedirect();

    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->not->toBeNull();
    expect($article->published_at->diffInSeconds(now()))->toBeLessThan(5);
    expect($article->last_edited_at)->toBeNull();
});

// ==========================================
// Edit Detection Tests
// ==========================================

it('sets last_edited_at when re-publishing already published article', function (): void {
    $originalPublishedAt = now()->subDay()->startOfSecond();
    $article = Article::factory()->published()->create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => 'Original content.',
        'published_at' => $originalPublishedAt,
        'last_edited_at' => null,
    ]);

    sleep(1); // Ensure time difference

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Published Article Updated',
        'slug' => 'published-article',
        'content' => 'Updated content.',
        'status' => 'published',
    ]);

    $response->assertRedirect();

    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->toEqual($originalPublishedAt);
    expect($article->last_edited_at)->not->toBeNull();
    expect($article->last_edited_at->diffInSeconds(now()))->toBeLessThan(5);
});

it('returns false from wasEdited accessor when last_edited_at is null', function (): void {
    $article = Article::factory()->published()->create([
        'published_at' => now(),
        'last_edited_at' => null,
    ]);

    expect($article->wasEdited())->toBeFalse();
});

it('returns true from wasEdited accessor when last_edited_at is set', function (): void {
    $article = Article::factory()->published()->create([
        'published_at' => now()->subDay(),
        'last_edited_at' => now(),
    ]);

    expect($article->wasEdited())->toBeTrue();
});

it('correctly handles published to draft to published flow', function (): void {
    // First publish
    $response = post(route('admin.articles.store'), [
        'title' => 'Flow Test Article',
        'slug' => 'flow-test-article',
        'content' => 'Original content.',
        'status' => 'published',
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'flow-test-article')->first();
    $firstPublishedAt = $article->published_at;
    expect($firstPublishedAt)->not->toBeNull();
    expect($article->last_edited_at)->toBeNull();

    sleep(1);

    // Change to draft
    put(route('admin.articles.update', $article), [
        'title' => 'Flow Test Article',
        'slug' => 'flow-test-article',
        'content' => 'Original content.',
        'status' => 'draft',
    ])->assertRedirect();

    $article->refresh();
    expect($article->status->value)->toBe('draft');

    sleep(1);

    // Re-publish
    put(route('admin.articles.update', $article), [
        'title' => 'Flow Test Article Updated',
        'slug' => 'flow-test-article',
        'content' => 'Updated content.',
        'status' => 'published',
    ])->assertRedirect();

    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->toEqual($firstPublishedAt);
    expect($article->last_edited_at)->not->toBeNull();
    expect($article->wasEdited())->toBeTrue();
});

// ==========================================
// Edge Case Tests
// ==========================================

it('creates draft article with null timestamps', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Draft With Null Timestamps',
        'slug' => 'draft-null-timestamps',
        'content' => 'Draft content.',
        'status' => 'draft',
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'draft-null-timestamps')->first();
    expect($article->published_at)->toBeNull();
    expect($article->last_edited_at)->toBeNull();
});

// ==========================================
// Unhappy Path Tests
// ==========================================

it('rejects invalid status values', function (string $invalidStatus): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Invalid Status Article',
        'slug' => 'invalid-status-article-'.str_replace(' ', '-', $invalidStatus),
        'content' => 'Content.',
        'status' => $invalidStatus,
    ]);

    $response->assertSessionHasErrors('status');
})->with([
    'invalid',
    'archived',
    'pending',
    'deleted',
]);

it('validates required fields on create', function (string $field, array $data): void {
    $response = post(route('admin.articles.store'), $data);
    $response->assertSessionHasErrors($field);
})->with([
    'title is required' => ['title', ['slug' => 'test', 'content' => 'content', 'status' => 'draft']],
    'slug is required' => ['slug', ['title' => 'Test', 'content' => 'content', 'status' => 'draft']],
    'content is required' => ['content', ['title' => 'Test', 'slug' => 'test', 'status' => 'draft']],
    'status is required' => ['status', ['title' => 'Test', 'slug' => 'test', 'content' => 'content']],
]);

it('validates required fields on update', function (string $field, array $data): void {
    $article = Article::factory()->draft()->create();

    $response = put(route('admin.articles.update', $article), $data);
    $response->assertSessionHasErrors($field);
})->with([
    'title is required' => ['title', ['slug' => 'test', 'content' => 'content', 'status' => 'draft']],
    'slug is required' => ['slug', ['title' => 'Test', 'content' => 'content', 'status' => 'draft']],
    'content is required' => ['content', ['title' => 'Test', 'slug' => 'test', 'status' => 'draft']],
    'status is required' => ['status', ['title' => 'Test', 'slug' => 'test', 'content' => 'content']],
]);

it('does not require published_at when creating published article', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'No Published At Required',
        'slug' => 'no-published-at-required',
        'content' => 'Content.',
        'status' => 'published',
        // published_at is intentionally not provided
    ]);

    // Should not fail validation for published_at
    $response->assertRedirect();
    expect(Article::where('slug', 'no-published-at-required')->exists())->toBeTrue();
});

it('does not require published_at when updating to published', function (): void {
    $article = Article::factory()->draft()->create([
        'slug' => 'update-no-published-at',
    ]);

    $response = put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => 'published',
        // published_at is intentionally not provided
    ]);

    // Should not fail validation for published_at
    $response->assertRedirect();
    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->not->toBeNull();
});

// ==========================================
// Model Accessor Tests
// ==========================================

it('wasEdited returns false for newly created published article', function (): void {
    $article = Article::factory()->published()->create([
        'last_edited_at' => null,
    ]);

    expect($article->wasEdited())->toBeFalse();
});

it('wasEdited returns true when last_edited_at is set', function (): void {
    $article = Article::factory()->published()->create([
        'last_edited_at' => now()->subHour(),
    ]);

    expect($article->wasEdited())->toBeTrue();
});

it('wasEdited returns false for draft articles', function (): void {
    $article = Article::factory()->draft()->create([
        'last_edited_at' => null,
    ]);

    expect($article->wasEdited())->toBeFalse();
});
