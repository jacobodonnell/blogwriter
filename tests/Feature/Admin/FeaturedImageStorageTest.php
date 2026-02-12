<?php

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
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

describe('uploaded images stored on correct disk based on photo status', function (): void {
    it('stores draft photo image on private disk initially', function (): void {
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

        // Photo owns the MediaLibrary, not Article
        $photo = $article->featuredPhoto;
        expect($photo)->not->toBeNull();
        expect($photo->status)->toBe(Status::Draft);

        $media = $photo->getFirstMedia('image');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('private');
    });

    it('stores published photo image on public disk initially', function (): void {
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

        // Photo owns the MediaLibrary, not Article
        $photo = $article->featuredPhoto;
        expect($photo)->not->toBeNull();
        expect($photo->status)->toBe(Status::Published);

        $media = $photo->getFirstMedia('image');
        expect($media)->not->toBeNull();
        expect($media->disk)->toBe('public');
    });
});

describe('photo status changes move images between disks', function (): void {
    it('moves image from private to public when photo status changes to published', function (): void {
        // Create draft photo with image
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $photo = Photo::factory()->draft()->create();

        // Upload image to draft photo (goes to private disk)
        $photo->addMedia($image->getRealPath())
            ->toMediaCollection('image', 'private');

        expect($photo->getFirstMedia('image')->disk)->toBe('private');

        // Update photo status to published (PhotoObserver moves media)
        $photo->update(['status' => Status::Published, 'published_at' => now()]);

        $photo->refresh();

        // Verify image moved to public disk
        $media = $photo->getFirstMedia('image');
        expect($media->disk)->toBe('public');
    });

    it('moves image from public to private when photo status changes to draft', function (): void {
        // Create published photo with image
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $photo = Photo::factory()->published()->create();

        // Upload image to published photo (goes to public disk)
        $photo->addMedia($image->getRealPath())
            ->toMediaCollection('image', 'public');

        expect($photo->getFirstMedia('image')->disk)->toBe('public');

        // Update photo status to draft (PhotoObserver moves media)
        $photo->update(['status' => Status::Draft, 'published_at' => null]);

        $photo->refresh();

        // Verify image moved to private disk
        $media = $photo->getFirstMedia('image');
        expect($media->disk)->toBe('private');
    });

    it('article status change does not affect photo disk when using existing photo', function (): void {
        // Create published photo (on public disk)
        $image = UploadedFile::fake()->image('test.jpg', 800, 600);
        $photo = Photo::factory()->published()->create();
        $photo->addMedia($image->getRealPath())
            ->toMediaCollection('image', 'public');

        // Create draft article using this published photo
        $article = Article::factory()->draft()->create(['photo_id' => $photo->id]);

        expect($article->status)->toBe(Status::Draft);
        expect($photo->status)->toBe(Status::Published);
        expect($photo->getFirstMedia('image')->disk)->toBe('public'); // Photo stays on public disk

        // Publishing the article does not move the photo
        $article->update(['status' => Status::Published, 'published_at' => now()]);

        $photo->refresh();
        expect($photo->getFirstMedia('image')->disk)->toBe('public'); // Photo still on public disk
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
