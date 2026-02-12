<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

describe('uploaded images stored on correct disk based on status', function (): void {
    it('stores draft article image on private disk initially', function (): void {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'Test content',
            'status' => 'draft',
            'image_source' => 'upload_file',
            'featured_image_file' => $image,
        ]);

        $article = Article::where('slug', 'draft-article')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Draft);

        $media = $article->getFirstMedia('featured_image');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('private');
    });

    it('stores published article image on public disk initially', function (): void {
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Published Article',
            'slug' => 'published-article',
            'content' => 'Test content',
            'status' => 'published',
            'image_source' => 'upload_file',
            'featured_image_file' => $image,
        ]);

        $article = Article::where('slug', 'published-article')->first();
        $response->assertRedirect(route('admin.articles.edit', $article));
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Published);

        $media = $article->getFirstMedia('featured_image');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('public');
    });
});

describe('status changes move images between disks', function (): void {
    it('moves image from private to public when draft becomes published', function (): void {
        // Create draft article with image
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $article = Article::factory()->draft()->create();

        // Upload image to draft article (goes to private disk)
        $article->addMedia($image->getRealPath())
            ->toMediaCollection('featured_image', 'private');

        expect($article->getFirstMedia('featured_image')->disk)->toBe('private');

        // Update to published
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
        ]);

        $article->refresh();
        $response->assertRedirect(route('admin.articles.edit', $article));

        // Verify image moved to public disk
        $media = $article->getFirstMedia('featured_image');
        expect($media->disk)->toBe('public');
    });

    it('moves image from public to private when published becomes draft', function (): void {
        // Create published article with image
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $article = Article::factory()->published()->create();

        // Upload image to published article (goes to public disk)
        $article->addMedia($image->getRealPath())
            ->toMediaCollection('featured_image', 'public');

        expect($article->getFirstMedia('featured_image')->disk)->toBe('public');

        // Update to draft
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'draft',
        ]);

        $article->refresh();
        $response->assertRedirect(route('admin.articles.edit', $article));

        // Verify image moved to private disk
        $media = $article->getFirstMedia('featured_image');
        expect($media->disk)->toBe('private');
    });

});

describe('enum comparison works correctly', function (): void {
    it('correctly identifies published status for disk selection', function (): void {
        $publishedStatus = Status::Published;
        $draftStatus = Status::Draft;

        // Test Status enum isPublic() method
        expect($publishedStatus->isPublic())->toBeTrue();
        expect($draftStatus->isPublic())->toBeFalse();

        // Test Status enum isPrivate() method
        expect($publishedStatus->isPrivate())->toBeFalse();
        expect($draftStatus->isPrivate())->toBeTrue();

        // Test enum comparison
        expect($publishedStatus === Status::Published)->toBeTrue();
        expect($draftStatus === Status::Published)->toBeFalse();
    });
});
