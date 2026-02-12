<?php

use App\Models\Article;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('photo gallery', function (): void {
    it('displays public photo gallery', function (): void {
        $response = $this->get(route('photos.index'));

        $response->assertSuccessful();
        $response->assertSee('Photos');
    });

    it('shows only published photos in gallery', function (): void {
        $publishedPhoto = Photo::factory()->published()->create(['filename' => 'published.jpg']);
        $draftPhoto = Photo::factory()->draft()->create(['filename' => 'draft.jpg']);

        $response = $this->get(route('photos.index'));

        $response->assertSuccessful();
        $response->assertSee('published.jpg');
        $response->assertDontSee('draft.jpg');
    });

    it('paginates photos', function (): void {
        Photo::factory()->count(25)->published()->create();

        $response = $this->get(route('photos.index'));

        $response->assertSuccessful();
        $response->assertSee('pagination', false);
    });

    it('shows photos ordered by published date descending', function (): void {
        $older = Photo::factory()->published()->create([
            'filename' => 'older.jpg',
            'published_at' => now()->subDays(2),
        ]);
        $newer = Photo::factory()->published()->create([
            'filename' => 'newer.jpg',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('photos.index'));

        $response->assertSuccessful();
        $content = $response->getContent();
        $newerPos = strpos($content, 'newer.jpg');
        $olderPos = strpos($content, 'older.jpg');

        expect($newerPos)->toBeLessThan($olderPos);
    });
});

describe('photo permalink', function (): void {
    it('displays published photo by slug', function (): void {
        $photo = Photo::factory()->published()->create([
            'slug' => 'my-photo',
            'caption' => 'My Photo Caption',
        ]);

        $response = $this->get(route('photos.show', $photo->slug));

        $response->assertSuccessful();
        $response->assertSee('My Photo Caption');
    });

    it('returns 404 for draft photos', function (): void {
        $photo = Photo::factory()->draft()->create(['slug' => 'draft-photo']);

        $response = $this->get(route('photos.show', $photo->slug));

        $response->assertNotFound();
    });

    it('returns 404 for non-existent photos', function (): void {
        $response = $this->get(route('photos.show', 'non-existent-slug'));

        $response->assertNotFound();
    });

    it('returns 404 for future-dated published photos', function (): void {
        $photo = Photo::factory()->create([
            'slug' => 'future-photo',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(route('photos.show', $photo->slug));

        $response->assertNotFound();
    });
});

describe('photo relationships', function (): void {
    it('shows articles using the photo', function (): void {
        $photo = Photo::factory()->published()->create();
        $article1 = Article::factory()->published()->create([
            'photo_id' => $photo->id,
            'title' => 'Article with Photo 1',
        ]);
        $article2 = Article::factory()->published()->create([
            'photo_id' => $photo->id,
            'title' => 'Article with Photo 2',
        ]);

        $response = $this->get(route('photos.show', $photo->slug));

        $response->assertSuccessful();
        $response->assertSee('Article with Photo 1');
        $response->assertSee('Article with Photo 2');
    });

    it('photo->articles() relationship returns correct articles', function (): void {
        $photo = Photo::factory()->published()->create();
        $article1 = Article::factory()->published()->create(['photo_id' => $photo->id]);
        $article2 = Article::factory()->published()->create(['photo_id' => $photo->id]);
        $otherArticle = Article::factory()->published()->create(); // Different photo

        $articles = $photo->articles;

        expect($articles->count())->toBe(2);
        expect($articles->pluck('id'))->toContain($article1->id);
        expect($articles->pluck('id'))->toContain($article2->id);
        expect($articles->pluck('id'))->not->toContain($otherArticle->id);
    });
});

describe('photo isPublic method', function (): void {
    it('returns true for published photo with past date', function (): void {
        $photo = Photo::factory()->published()->create([
            'published_at' => now()->subDay(),
        ]);

        expect($photo->isPublic())->toBeTrue();
    });

    it('returns false for draft photo', function (): void {
        $photo = Photo::factory()->draft()->create();

        expect($photo->isPublic())->toBeFalse();
    });

    it('returns false for published photo with future date', function (): void {
        $photo = Photo::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        expect($photo->isPublic())->toBeFalse();
    });

    it('returns false for published photo without published_at', function (): void {
        $photo = Photo::factory()->create([
            'status' => 'published',
            'published_at' => null,
        ]);

        expect($photo->isPublic())->toBeFalse();
    });
});

describe('photo external url', function (): void {
    it('displays external URL photo', function (): void {
        $photo = Photo::factory()->published()->create([
            'slug' => 'external-photo',
            'meta' => ['external_url' => 'https://example.com/image.jpg'],
        ]);

        $response = $this->get(route('photos.show', $photo->slug));

        $response->assertSuccessful();
        $response->assertSee('https://example.com/image.jpg', false);
    });
});
