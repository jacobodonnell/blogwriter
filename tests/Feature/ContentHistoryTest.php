<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('has a nullable json content_history column on articles', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    expect($article->content_history)->toBeNull();

    $article->update(['content_history' => ['entries' => ['# Hello'], 'pointer' => 0]]);
    $article->refresh();

    expect($article->content_history)->toBe(['entries' => ['# Hello'], 'pointer' => 0]);
});

it('appends a snapshot on preview when content changes', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Original content',
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Updated content',
        ]);

    $article->refresh();
    $history = $article->content_history;

    expect($history)->not->toBeNull()
        ->and($history['entries'])->toHaveCount(1)
        ->and($history['entries'][0])->toBe('## Original content')
        ->and($history['pointer'])->toBe(1);
});

it('does not duplicate snapshot when content is unchanged', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Original content',
    ]);

    // First preview — changes content, should append snapshot
    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Updated content',
        ]);

    // Second preview — same content as first preview, no new snapshot
    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Updated content',
        ]);

    $article->refresh();
    $history = $article->content_history;

    expect($history['entries'])->toHaveCount(1);
});

it('caps snapshots at 50 entries', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Version 0',
    ]);

    // Seed with 49 entries already at pointer 49
    $entries = [];
    for ($i = 1; $i <= 49; $i++) {
        $entries[] = "## Version {$i}";
    }
    $article->update([
        'content_history' => ['entries' => $entries, 'pointer' => 49],
    ]);

    // 50th different content — should push to 50, then the 51st should cap
    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Version 50',
        ]);

    $article->refresh();
    expect($article->content_history['entries'])->toHaveCount(50);

    // 51st — should still be 50 (oldest dropped)
    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Version 51',
        ]);

    $article->refresh();
    $history = $article->content_history;

    expect($history['entries'])->toHaveCount(50)
        ->and($history['entries'][0])->not->toBe('## Version 1');
});

it('clears content_history on full save', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Original',
        'content_history' => ['entries' => ['## Old'], 'pointer' => 1],
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## Final version',
            'status' => 'draft',
        ]);

    $article->refresh();

    expect($article->content_history)->toBeNull()
        ->and($article->draft)->toBeNull();
});

it('syncs history_pointer from request', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => '## Original',
        'content_history' => ['entries' => ['## V1', '## V2'], 'pointer' => 2],
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.articles.preview.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => '## V3',
            'history_pointer' => 1,
        ]);

    $article->refresh();
    $history = $article->content_history;

    // Pointer was 1, so entries after index 1 should be pruned, then new content appended
    expect($history['entries'])->toHaveCount(2)
        ->and($history['entries'][0])->toBe('## V1')
        ->and($history['entries'][1])->toBe('## Original')
        ->and($history['pointer'])->toBe(2);
});

it('passes contentHistory to alpine config on edit page', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content_history' => ['entries' => ['## V1'], 'pointer' => 1],
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('admin.articles.edit', $article));

    $response->assertSuccessful();

    // The alpine config JSON should contain contentHistory (Blade @js uses &quot; for quotes)
    $response->assertSee('contentHistory', false);
});
