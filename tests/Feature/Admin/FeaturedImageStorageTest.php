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
});

describe('uploaded images stored on correct disk based on status', function (): void {
    it('stores draft article image on private disk initially', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Draft Article',
            'slug' => 'draft-article',
            'content' => 'Test content',
            'status' => 'draft',
            'image_source' => 'upload_file',
            'featured_image_file' => $image,
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'draft-article')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Draft);

        // Verify initial upload went to private disk
        // The controller should store to private disk for draft status
        $privateFiles = Storage::disk('private')->allFiles('articles/featured');
        expect($privateFiles)->not->toBeEmpty();

        // Verify nothing was stored on public disk for draft
        expect(Storage::disk('public')->allFiles('articles/featured'))->toBeEmpty();
    });

    it('stores hidden article image on private disk initially', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Hidden Article',
            'slug' => 'hidden-article',
            'content' => 'Test content',
            'status' => 'hidden',
            'image_source' => 'upload_file',
            'featured_image_file' => $image,
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'hidden-article')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Hidden);

        // Verify initial upload went to private disk
        $privateFiles = Storage::disk('private')->allFiles('articles/featured');
        expect($privateFiles)->not->toBeEmpty();

        // Verify nothing was stored on public disk for hidden
        expect(Storage::disk('public')->allFiles('articles/featured'))->toBeEmpty();
    });

    it('stores published article image on public disk initially', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post(route('admin.articles.store'), [
            'title' => 'Published Article',
            'slug' => 'published-article',
            'content' => 'Test content',
            'status' => 'published',
            'image_source' => 'upload_file',
            'featured_image_file' => $image,
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::where('slug', 'published-article')->first();
        expect($article)->not->toBeNull();
        expect($article->status)->toBe(Status::Published);

        // Verify initial upload went to public disk
        $publicFiles = Storage::disk('public')->allFiles('articles/featured');
        expect($publicFiles)->not->toBeEmpty();

        // Verify nothing was stored on private disk for published
        expect(Storage::disk('private')->allFiles('articles/featured'))->toBeEmpty();
    });
});

describe('status changes move images between disks', function (): void {
    it('moves image from private to public when draft becomes published', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create draft article with image
        $article = Article::factory()->draft()->create();

        // Simulate processed images on private disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('private')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('private')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Update to published
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Verify images moved to public disk
        Storage::disk('public')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertExists("articles/featured/{$articleId}/full-thumbnail.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$articleId}/full.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$articleId}/full-thumbnail.webp");
    });

    it('moves image from public to private when published becomes draft', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create published article with image
        $article = Article::factory()->published()->create();

        // Simulate processed images on public disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('public')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('public')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Update to draft
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Verify images moved to private disk
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full-thumbnail.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full-thumbnail.webp");
    });

    it('moves image from public to private when published becomes hidden', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create published article with image
        $article = Article::factory()->published()->create();

        // Simulate processed images on public disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('public')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('public')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Update to hidden
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'hidden',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Verify images moved to private disk
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full-thumbnail.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full-thumbnail.webp");
    });

    it('moves image from private to public when hidden becomes published', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create hidden article with image
        $article = Article::factory()->hidden()->create();

        // Simulate processed images on private disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('private')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('private')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Update to published
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Verify images moved to public disk
        Storage::disk('public')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertExists("articles/featured/{$articleId}/full-thumbnail.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$articleId}/full.webp");
        Storage::disk('private')->assertMissing("articles/featured/{$articleId}/full-thumbnail.webp");
    });

    it('keeps image on private disk when draft becomes hidden', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create draft article with image
        $article = Article::factory()->draft()->create();

        // Simulate processed images on private disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('private')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('private')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Refresh article to clear dirty attributes
        $article->refresh();

        // Update to hidden
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'hidden',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Both draft and hidden use private disk, so no move should occur
        // Verify image stayed on private disk
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full.webp");
    });

    it('keeps image on private disk when hidden becomes draft', function (): void {
        Storage::fake('public');
        Storage::fake('private');

        // Create hidden article with image
        $article = Article::factory()->hidden()->create();

        // Simulate processed images on private disk
        $articleId = $article->id;
        $article->featured_image = "articles/featured/{$articleId}/full.webp";
        $article->saveQuietly();
        Storage::disk('private')->put("articles/featured/{$articleId}/full.webp", 'test-content');
        Storage::disk('private')->put("articles/featured/{$articleId}/full-thumbnail.webp", 'test-thumb');

        // Refresh article to clear dirty attributes
        $article->refresh();

        // Update to draft
        $response = $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        // Both hidden and draft use private disk, so no move should occur
        // Verify image stayed on private disk
        Storage::disk('private')->assertExists("articles/featured/{$articleId}/full.webp");
        Storage::disk('public')->assertMissing("articles/featured/{$articleId}/full.webp");
    });
});

describe('enum comparison works correctly', function (): void {
    it('correctly identifies published status for disk selection', function (): void {
        $publishedStatus = Status::Published;
        $draftStatus = Status::Draft;
        $hiddenStatus = Status::Hidden;

        // Test Status enum isPublic() method
        expect($publishedStatus->isPublic())->toBeTrue();
        expect($draftStatus->isPublic())->toBeFalse();
        expect($hiddenStatus->isPublic())->toBeFalse();

        // Test Status enum isPrivate() method
        expect($publishedStatus->isPrivate())->toBeFalse();
        expect($draftStatus->isPrivate())->toBeTrue();
        expect($hiddenStatus->isPrivate())->toBeTrue();

        // Test enum comparison (the bug)
        expect($publishedStatus === Status::Published)->toBeTrue();
        expect($draftStatus === Status::Published)->toBeFalse();
        expect($hiddenStatus === Status::Published)->toBeFalse();

        // Test WRONG comparison (the bug in Article.php:136 - NOW FIXED)
        expect($publishedStatus === Status::Published->value)->toBeFalse(); // This is FALSE (was the bug!)
        expect($publishedStatus->value === Status::Published->value)->toBeTrue(); // This is correct
    });
});
