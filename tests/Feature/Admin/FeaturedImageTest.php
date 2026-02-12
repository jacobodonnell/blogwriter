<?php

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Storage::fake('public');
    Storage::fake('private');
});

it('creates photo from external URL and links to article', function (): void {
    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image' => 'https://example.com/image.jpg',
        ])
        ->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto)->toBeInstanceOf(Photo::class);
    expect($article->featuredPhoto->meta['external_url'])->toBe('https://example.com/image.jpg');
    expect($article->featuredPhoto->isExternalUrl())->toBeTrue();
});

it('creates photo from file upload and links to article', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => 'draft',
            'featured_image_file' => $file,
        ])
        ->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto)->toBeInstanceOf(Photo::class);
    expect($article->featuredPhoto->getFirstMedia('image'))->not->toBeNull();
    expect($article->featuredPhoto->isExternalUrl())->toBeFalse();
});

it('links existing photo to article', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => 'draft',
            'photo_id' => $photo->id,
        ])
        ->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBe($photo->id);
    expect($article->featuredPhoto->id)->toBe($photo->id);
});

it('removes featured photo link when checkbox is checked', function (): void {
    $photo = Photo::factory()->published()->create();
    $article = Article::factory()->create(['photo_id' => $photo->id]);

    $this->actingAs($this->user)
        ->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => $article->status->value,
            'remove_featured_image' => '1',
        ])
        ->assertRedirect();

    $article->refresh();

    expect($article->photo_id)->toBeNull();
    expect($article->featuredPhoto)->toBeNull();
});

it('allows creating article without featured photo', function (): void {
    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Test content',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBeNull();
    expect($article->featuredPhoto)->toBeNull();
});
