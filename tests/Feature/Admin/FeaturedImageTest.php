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

// ============================================================================
// HAPPY PATH TESTS
// ============================================================================

describe('happy paths', function (): void {
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
});

// ============================================================================
// VALIDATION TESTS
// ============================================================================

describe('validation', function (): void {
    it('rejects invalid file types', function (): void {
        $file = UploadedFile::fake()->create('malware.exe', 100);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors('featured_image_file');
    });

    it('rejects oversized files', function (): void {
        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors('featured_image_file');
    });

    it('rejects invalid URLs', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'not-a-valid-url',
            ])
            ->assertSessionHasErrors('featured_image');
    });

    it('rejects when both URL and file are provided', function (): void {
        $file = UploadedFile::fake()->image('featured.jpg');

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => 'https://example.com/image.jpg',
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors(['featured_image', 'featured_image_file']);
    });

    it('rejects invalid photo_id', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'photo_id' => 99999, // Non-existent photo ID
            ])
            ->assertSessionHasErrors('photo_id');
    });

    it('rejects when photo_id and URL are both provided', function (): void {
        $photo = Photo::factory()->published()->create();

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'photo_id' => $photo->id,
                'featured_image' => 'https://example.com/image.jpg',
            ])
            ->assertSessionHasErrors(['photo_id', 'featured_image']);
    });

    it('rejects when photo_id and file are both provided', function (): void {
        $photo = Photo::factory()->published()->create();
        $file = UploadedFile::fake()->image('test.jpg');

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'photo_id' => $photo->id,
                'featured_image_file' => $file,
            ])
            ->assertSessionHasErrors(['photo_id', 'featured_image_file']);
    });

    it('enforces maximum URL length', function (): void {
        $longUrl = 'https://example.com/'.str_repeat('a', 500);

        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
                'featured_image' => $longUrl,
            ])
            ->assertSessionHasErrors('featured_image');
    });
});

// ============================================================================
// EDGE CASES
// ============================================================================

describe('edge cases', function (): void {
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

    it('handles featured photo update on existing article', function (): void {
        $oldPhoto = Photo::factory()->published()->create();
        $newPhoto = Photo::factory()->published()->create();
        $article = Article::factory()->create(['photo_id' => $oldPhoto->id]);

        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'photo_id' => $newPhoto->id,
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->photo_id)->toBe($newPhoto->id);
        expect($article->featuredPhoto->id)->toBe($newPhoto->id);
    });

    it('creates photo with article status', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Published Article',
                'slug' => 'published-article',
                'content' => 'Test content',
                'status' => 'published',
                'featured_image' => 'https://example.com/image.jpg',
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->status->value)->toBe('published');
        expect($article->featuredPhoto->status->value)->toBe('published');
    });
});
