<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Storage::fake('public');
    Storage::fake('private');
});

it('displays featured image form with photo select and upload button', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('name="photo_id"', false)
        ->assertSee('Upload New', false)
        ->assertSee('name="featured_image"', false);
});

it('creates article with uploaded featured image file as published photo', function (): void {
    $file = UploadedFile::fake()->image('featured.jpg', 1200, 800);

    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Article With Featured Image',
            'slug' => 'article-with-featured-image',
            'content' => 'Content.',
            'status' => Status::Public->value,
            'featured_image_file' => $file,
        ]);

    $article = Article::where('slug', 'article-with-featured-image')->first();
    expect($article)->not->toBeNull()
        ->and($article->photo_id)->not->toBeNull();

    $photo = Photo::find($article->photo_id);
    expect($photo)->not->toBeNull()
        ->and($photo->status->value)->toBe('public')
        ->and($photo->getFirstMedia('image'))->not->toBeNull()
        ->and($photo->getFirstMedia('image')->disk)->toBe('public');
});

it('validates featured image file type', function (): void {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $this->actingAs($this->user)
        ->post(route('admin.articles.store'), [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Content.',
            'status' => Status::Public->value,
            'featured_image_file' => $file,
        ])
        ->assertSessionHasErrors('featured_image_file');
});

it('updates article to add featured image', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();
    $file = UploadedFile::fake()->image('new-featured.jpg');

    expect($article->photo_id)->toBeNull();

    $this->actingAs($this->user)
        ->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => Status::Public->value,
            'featured_image_file' => $file,
        ]);

    expect($article->fresh()->photo_id)->not->toBeNull();
});

it('updates article to change featured image', function (): void {
    $oldPhoto = Photo::factory()->published()->for($this->user)->create();
    $article = Article::factory()->published()->for($this->user)->create(['photo_id' => $oldPhoto->id]);

    $file = UploadedFile::fake()->image('new-featured.jpg');

    $this->actingAs($this->user)
        ->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => Status::Public->value,
            'featured_image_file' => $file,
        ]);

    expect($article->fresh()->photo_id)->not->toBe($oldPhoto->id);
});
