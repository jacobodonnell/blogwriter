<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('saves custom caption in meta on store', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Caption Test Article',
        'content' => '## Some content here',
        'status' => Status::Draft->value,
        'meta' => [
            'featured_image_caption' => 'My custom caption',
        ],
        'featured_image' => 'https://example.com/image.jpg',
    ])->assertRedirect();

    $article = Article::latest()->first();
    expect($article->meta['featured_image_caption'])->toBe('My custom caption');
});

it('saves custom caption in meta on update', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => '## Updated content',
        'status' => Status::Draft->value,
        'meta' => [
            'featured_image_caption' => 'Updated caption',
        ],
        'featured_image' => 'https://example.com/image.jpg',
    ])->assertRedirect();

    $article->refresh();
    expect($article->meta['featured_image_caption'])->toBe('Updated caption');
});

it('saves use_photo_caption flag and clears featured_image_caption', function (): void {
    $photo = Photo::factory()->published()->for($this->user)->create([
        'caption' => 'Photo native caption',
    ]);

    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
    ]);

    put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => '## Content here',
        'status' => Status::Draft->value,
        'photo_id' => $photo->id,
        'meta' => [
            'use_photo_caption' => '1',
            'featured_image_caption' => '',
        ],
    ])->assertRedirect();

    $article->refresh();
    expect($article->meta)->toHaveKey('use_photo_caption')
        ->and($article->meta)->not->toHaveKey('featured_image_caption');
});

it('accessor returns custom caption from meta', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'meta' => ['featured_image_caption' => 'Custom caption text'],
    ]);

    expect($article->featured_image_caption)->toBe('Custom caption text');
});

it('accessor returns photo caption when toggle enabled', function (): void {
    $photo = Photo::factory()->published()->for($this->user)->create([
        'caption' => 'Photo caption here',
    ]);

    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
        'meta' => ['use_photo_caption' => '1'],
    ]);

    expect($article->featured_image_caption)->toBe('Photo caption here');
});

it('accessor returns null when no caption set', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'meta' => [],
    ]);

    expect($article->featured_image_caption)->toBeNull();
});

it('clears caption meta when featured image removed', function (): void {
    $photo = Photo::factory()->published()->for($this->user)->create();

    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
        'meta' => [
            'featured_image_caption' => 'Old caption',
        ],
    ]);

    put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => '## Content',
        'status' => Status::Draft->value,
        'remove_featured_image' => true,
    ])->assertRedirect();

    $article->refresh();
    expect($article->meta)->not->toHaveKey('featured_image_caption')
        ->and($article->meta)->not->toHaveKey('use_photo_caption');
});

it('renders figcaption on public article page when caption exists', function (): void {
    $article = Article::factory()->published()->for($this->user)->create([
        'meta' => [
            'featured_image_url' => 'https://example.com/image.jpg',
            'featured_image_caption' => 'A beautiful sunset',
        ],
    ]);

    get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('A beautiful sunset')
        ->assertSee('<figcaption', false);
});

it('omits figcaption on public article page when no caption', function (): void {
    $article = Article::factory()->published()->for($this->user)->create([
        'meta' => [
            'featured_image_url' => 'https://example.com/image.jpg',
        ],
    ]);

    get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertDontSee('<figcaption', false);
});
