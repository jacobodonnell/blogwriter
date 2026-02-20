<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('creates article with published status and auto-sets published_at', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Test Published Article',
        'slug' => 'test-published-article',
        'content' => 'This is the article content.',
        'status' => Status::Published->value,
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'test-published-article')->first();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->not->toBeNull();
    expect($article->last_edited_at)->toBeNull();
});

it('creates article with draft status and keeps published_at null', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Test Draft Article',
        'slug' => 'test-draft-article',
        'content' => 'This is the draft content.',
        'status' => Status::Draft->value,
    ]);

    $response->assertRedirect();

    $article = Article::where('slug', 'test-draft-article')->first();
    expect($article->status->value)->toBe('draft');
    expect($article->published_at)->toBeNull();
});

it('updates draft to published and auto-sets published_at', function (): void {
    $article = Article::factory()->draft()->create([
        'title' => 'Draft Article',
        'slug' => 'draft-to-published',
        'published_at' => null,
    ]);

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Draft Article',
        'slug' => 'draft-to-published',
        'content' => 'Updated content.',
        'status' => Status::Published->value,
    ]);

    $response->assertRedirect();

    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->not->toBeNull();
});

it('sets last_edited_at when re-publishing already published article', function (): void {
    $originalPublishedAt = now()->subDay()->startOfSecond();
    $article = Article::factory()->published()->create([
        'published_at' => $originalPublishedAt,
        'last_edited_at' => null,
    ]);

    $this->travel(1)->seconds();

    $response = put(route('admin.articles.update', $article), [
        'title' => 'Published Article Updated',
        'slug' => $article->slug,
        'content' => 'Updated content.',
        'status' => Status::Published->value,
    ]);

    $response->assertRedirect();

    $article->refresh();
    expect($article->published_at)->toEqual($originalPublishedAt);
    expect($article->last_edited_at)->not->toBeNull();
});

it('correctly handles published to draft to published flow', function (): void {
    $response = post(route('admin.articles.store'), [
        'title' => 'Flow Test Article',
        'slug' => 'flow-test-article',
        'content' => 'Original content.',
        'status' => Status::Published->value,
    ]);

    $article = Article::where('slug', 'flow-test-article')->first();
    $firstPublishedAt = $article->published_at;

    $this->travel(1)->seconds();

    put(route('admin.articles.update', $article), [
        'title' => 'Flow Test Article',
        'slug' => 'flow-test-article',
        'content' => 'Original content.',
        'status' => Status::Draft->value,
    ]);

    $article->refresh();
    expect($article->status->value)->toBe('draft');

    $this->travel(1)->seconds();

    put(route('admin.articles.update', $article), [
        'title' => 'Flow Test Article Updated',
        'slug' => 'flow-test-article',
        'content' => 'Updated content.',
        'status' => Status::Published->value,
    ]);

    $article->refresh();
    expect($article->status->value)->toBe('published');
    expect($article->published_at)->toEqual($firstPublishedAt);
    expect($article->last_edited_at)->not->toBeNull();
    expect($article->wasEdited())->toBeTrue();
});

it('wasEdited accessor returns correct value', function (): void {
    $newArticle = Article::factory()->published()->create(['last_edited_at' => null]);
    $editedArticle = Article::factory()->published()->create(['last_edited_at' => now()->subHour()]);

    expect($newArticle->wasEdited())->toBeFalse();
    expect($editedArticle->wasEdited())->toBeTrue();
});
