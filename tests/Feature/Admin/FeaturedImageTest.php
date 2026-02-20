<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

it('stores external URL in article meta instead of creating a photo', function (): void {
    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Draft->value,
        'featured_image' => 'https://example.com/image.jpg',
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBeNull();
    expect($article->meta['featured_image_url'])->toBe('https://example.com/image.jpg');
    expect($article->featured_image_url)->toBe('https://example.com/image.jpg');
});

it('creates photo from file upload and links to article', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Published->value,
        'featured_image_file' => $file,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto)->toBeInstanceOf(Photo::class);
    expect($article->featuredPhoto->status->value)->toBe('published');
    expect($article->featuredPhoto->getFirstMedia('image'))->not->toBeNull();
    expect($article->featuredPhoto->getFirstMedia('image')->disk)->toBe('public');
});

it('creates a published photo even when article status is draft', function (): void {
    $file = UploadedFile::fake()->image('draft-article-image.jpg');

    $this->post(route('admin.articles.store'), [
        'title' => 'Draft Article With Image',
        'slug' => 'draft-article-with-image',
        'content' => 'Draft content',
        'status' => Status::Draft->value,
        'featured_image_file' => $file,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->status->value)->toBe('draft');
    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto->status->value)->toBe('published');
    expect($article->featuredPhoto->getFirstMedia('image')->disk)->toBe('public');
});

it('preserves featured photo when changing published article to draft', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);
    $article = Article::factory()->published()->create([
        'user_id' => $this->user->id,
        'photo_id' => $photo->id,
    ]);

    $this->put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $article->refresh();
    $photo->refresh();

    expect($article->status->value)->toBe('draft');
    expect($article->photo_id)->toBe($photo->id);
    expect($photo->status->value)->toBe('published');
});

it('links existing photo to article', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);

    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Draft->value,
        'photo_id' => $photo->id,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBe($photo->id);
    expect($article->featuredPhoto->id)->toBe($photo->id);
});

it('removes featured photo link when checkbox is checked', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);
    $article = Article::factory()->create(['user_id' => $this->user->id, 'photo_id' => $photo->id]);

    $this->put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => $article->status->value,
        'remove_featured_image' => '1',
    ])->assertRedirect();

    $article->refresh();

    expect($article->photo_id)->toBeNull();
    expect($article->featuredPhoto)->toBeNull();
});

it('allows creating article without featured photo', function (): void {
    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBeNull();
    expect($article->featuredPhoto)->toBeNull();
});

it('setting photo_id clears meta.featured_image_url on save', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'meta' => ['featured_image_url' => 'https://example.com/old.jpg'],
    ]);

    $article->photo_id = $photo->id;
    $article->save();
    $article->refresh();

    expect($article->photo_id)->toBe($photo->id);
    expect($article->meta)->not->toHaveKey('featured_image_url');
});

it('returns title as fallback alt text', function (): void {
    $article = Article::factory()->create(['title' => 'Test Title']);

    expect($article->featured_image_alt)->toBe('Test Title');
});

it('returns meta alt text when set', function (): void {
    $article = Article::factory()->create([
        'meta' => ['featured_image_alt' => 'Custom alt text'],
    ]);

    expect($article->featured_image_alt)->toBe('Custom alt text');
});

it('returns photo alt text when photo has alt_text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Photo alt description']);
    $article = Article::factory()->create(['photo_id' => $photo->id]);

    expect($article->featured_image_alt)->toBe('Photo alt description');
});

it('prefers meta alt over photo alt text', function (): void {
    $photo = Photo::factory()->create(['alt_text' => 'Photo alt']);
    $article = Article::factory()->create([
        'photo_id' => $photo->id,
        'meta' => ['featured_image_alt' => 'Meta alt'],
    ]);

    expect($article->featured_image_alt)->toBe('Meta alt');
});
