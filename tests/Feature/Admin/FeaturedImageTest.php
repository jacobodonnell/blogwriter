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
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

it('stores external URL in article meta instead of creating a photo', function (): void {
    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Private->value,
        'featured_image' => 'https://example.com/image.jpg',
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBeNull();
    expect($article->external_featured_img_url)->toBe('https://example.com/image.jpg');
    expect($article->featured_image_url)->toBe('https://example.com/image.jpg');
});

it('creates photo from file upload and links to article', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Public->value,
        'featured_image_file' => $file,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto)->toBeInstanceOf(Photo::class);
    expect($article->featuredPhoto->status->value)->toBe('public');
    expect($article->featuredPhoto->getFirstMedia('image'))->not->toBeNull();
    expect($article->featuredPhoto->getFirstMedia('image')->disk)->toBe('public');
});

it('creates a published photo even when article status is draft', function (): void {
    $file = UploadedFile::fake()->image('draft-article-image.jpg');

    $this->post(route('admin.articles.store'), [
        'title' => 'Draft Article With Image',
        'slug' => 'draft-article-with-image',
        'content' => 'Draft content',
        'status' => Status::Private->value,
        'featured_image_file' => $file,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->status->value)->toBe('private');
    expect($article->photo_id)->not->toBeNull();
    expect($article->featuredPhoto->status->value)->toBe('public');
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
        'status' => Status::Private->value,
    ])->assertRedirect();

    $article->refresh();
    $photo->refresh();

    expect($article->status->value)->toBe('private');
    expect($article->photo_id)->toBe($photo->id);
    expect($photo->status->value)->toBe('public');
});

it('links existing photo to article', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);

    $this->post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => 'Test content',
        'status' => Status::Private->value,
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
        'status' => Status::Private->value,
    ])->assertRedirect();

    $article = Article::first();

    expect($article->photo_id)->toBeNull();
    expect($article->featuredPhoto)->toBeNull();
});

it('setting photo_id clears external_featured_img_url on save', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'external_featured_img_url' => 'https://example.com/old.jpg',
    ]);

    $article->photo_id = $photo->id;
    $article->save();
    $article->refresh();

    expect($article->photo_id)->toBe($photo->id);
    expect($article->external_featured_img_url)->toBeNull();
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

it('saves featured_image_alt to meta when provided with external URL', function (): void {
    $this->post(route('admin.articles.store'), [
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
        'status' => Status::Private->value,
        'featured_image' => 'https://example.com/image.jpg',
        'featured_image_alt' => 'A scenic mountain view',
    ])->assertRedirect();

    $article = Article::first();
    expect($article->featured_image_alt)->toBe('A scenic mountain view');
    expect($article->meta['featured_image_alt'])->toBe('A scenic mountain view');
});

it('silently saves title as featured_image_alt when external URL is set but alt is empty', function (): void {
    $this->post(route('admin.articles.store'), [
        'title' => 'My Great Post',
        'slug' => 'my-great-post',
        'content' => 'Content',
        'status' => Status::Private->value,
        'featured_image' => 'https://example.com/image.jpg',
        'featured_image_alt' => '',
    ])->assertRedirect();

    $article = Article::first();
    expect($article->meta['featured_image_alt'])->toBe('My Great Post');
    expect($article->featured_image_alt)->toBe('My Great Post');
});

it('does not save featured_image_alt to meta when photo is selected and alt is empty', function (): void {
    $photo = Photo::factory()->published()->create(['alt_text' => 'Photo alt', 'user_id' => $this->user->id]);

    $this->post(route('admin.articles.store'), [
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
        'status' => Status::Private->value,
        'photo_id' => $photo->id,
    ])->assertRedirect();

    $article = Article::first();
    expect($article->meta)->not->toHaveKey('featured_image_alt');
    expect($article->featured_image_alt)->toBe('Photo alt');
});

it('clears featured_image_alt from meta when featured image is removed', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'external_featured_img_url' => 'https://example.com/image.jpg',
        'meta' => ['featured_image_alt' => 'Old alt'],
    ]);

    $this->put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => $article->status->value,
        'remove_featured_image' => '1',
    ])->assertRedirect();

    $article->refresh();
    expect($article->meta)->not->toHaveKey('featured_image_alt');
});
