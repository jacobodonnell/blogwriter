<?php

use App\Models\Article;
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
    it('persists external URL to database', function (): void {
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

        expect($article->featured_image)->toBe('https://example.com/image.jpg');
    });

    it('removes featured image when checkbox is checked', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/image.jpg',
        ]);

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

        expect($article->featured_image)->toBeNull();
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
    it('allows creating article without featured image', function (): void {
        $this->actingAs($this->user)
            ->post(route('admin.articles.store'), [
                'title' => 'Test Article',
                'slug' => 'test-article',
                'content' => 'Test content',
                'status' => 'draft',
            ])
            ->assertRedirect();

        $article = Article::first();

        expect($article->featured_image)->toBeNull();
    });

    it('handles featured image update on existing article', function (): void {
        $article = Article::factory()->create([
            'featured_image' => 'https://example.com/old-image.jpg',
        ]);

        $this->actingAs($this->user)
            ->put(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'content' => $article->content,
                'status' => $article->status->value,
                'featured_image' => 'https://example.com/new-image.jpg',
            ])
            ->assertRedirect();

        $article->refresh();

        expect($article->featured_image)->toBe('https://example.com/new-image.jpg');
    });
});
