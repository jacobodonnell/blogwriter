<?php

use App\Enums\Status;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('photo factory', function (): void {
    it('creates photo with default state', function (): void {
        $photo = Photo::factory()->create();

        expect($photo->filename)->toBeString();
        expect($photo->slug)->toBeString();
        expect($photo->alt_text)->toBeString();
        expect($photo->status)->toBeInstanceOf(Status::class);
    });

    it('creates published photo', function (): void {
        $photo = Photo::factory()->published()->create();

        expect($photo->status)->toBe(Status::Published);
        expect($photo->published_at)->not->toBeNull();
    });

    it('creates draft photo', function (): void {
        $photo = Photo::factory()->draft()->create();

        expect($photo->status)->toBe(Status::Draft);
        expect($photo->published_at)->toBeNull();
    });
});

describe('published scope', function (): void {
    it('returns only published photos', function (): void {
        $published1 = Photo::factory()->published()->create();
        $published2 = Photo::factory()->published()->create();
        $draft = Photo::factory()->draft()->create();

        $publishedPhotos = Photo::published()->get();

        expect($publishedPhotos->count())->toBe(2);
        expect($publishedPhotos->pluck('id'))->toContain($published1->id);
        expect($publishedPhotos->pluck('id'))->toContain($published2->id);
        expect($publishedPhotos->pluck('id'))->not->toContain($draft->id);
    });

    it('excludes future-dated photos', function (): void {
        $published = Photo::factory()->published()->create([
            'published_at' => now()->subDay(),
        ]);
        $future = Photo::factory()->create([
            'status' => Status::Published,
            'published_at' => now()->addDay(),
        ]);

        $publishedPhotos = Photo::published()->get();

        expect($publishedPhotos->count())->toBe(1);
        expect($publishedPhotos->pluck('id'))->toContain($published->id);
        expect($publishedPhotos->pluck('id'))->not->toContain($future->id);
    });

    it('excludes photos without published_at', function (): void {
        $published = Photo::factory()->published()->create();
        $noDate = Photo::factory()->create([
            'status' => Status::Published,
            'published_at' => null,
        ]);

        $publishedPhotos = Photo::published()->get();

        expect($publishedPhotos->count())->toBe(1);
        expect($publishedPhotos->pluck('id'))->toContain($published->id);
        expect($publishedPhotos->pluck('id'))->not->toContain($noDate->id);
    });
});

describe('isExternalUrl method', function (): void {
    it('returns true when photo has external_url in meta', function (): void {
        $photo = Photo::factory()->create([
            'meta' => ['external_url' => 'https://example.com/image.jpg'],
        ]);

        expect($photo->isExternalUrl())->toBeTrue();
    });

    it('returns false when photo does not have external_url', function (): void {
        $photo = Photo::factory()->create(['meta' => []]);

        expect($photo->isExternalUrl())->toBeFalse();
    });

    it('returns false when meta is null', function (): void {
        $photo = Photo::factory()->create(['meta' => null]);

        expect($photo->isExternalUrl())->toBeFalse();
    });
});

describe('imageUrl accessor', function (): void {
    it('returns external_url when present', function (): void {
        $photo = Photo::factory()->create([
            'meta' => ['external_url' => 'https://example.com/image.jpg'],
        ]);

        expect($photo->imageUrl)->toBe('https://example.com/image.jpg');
    });

    it('returns null when no external_url and no media', function (): void {
        $photo = Photo::factory()->create(['meta' => []]);

        expect($photo->imageUrl)->toBeNull();
    });
});

describe('slug uniqueness', function (): void {
    it('enforces unique slugs', function (): void {
        Photo::factory()->create(['slug' => 'unique-slug']);

        expect(function (): void {
            Photo::factory()->create(['slug' => 'unique-slug']);
        })->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows different slugs', function (): void {
        Photo::factory()->create(['slug' => 'first-slug']);
        Photo::factory()->create(['slug' => 'second-slug']);

        expect(Photo::count())->toBe(2);
    });
});

describe('isPublic method', function (): void {
    it('returns true for published photo with past date', function (): void {
        $photo = Photo::factory()->create([
            'status' => Status::Published,
            'published_at' => now()->subDay(),
        ]);

        expect($photo->isPublic())->toBeTrue();
    });

    it('returns false for draft photo', function (): void {
        $photo = Photo::factory()->create([
            'status' => Status::Draft,
            'published_at' => now(),
        ]);

        expect($photo->isPublic())->toBeFalse();
    });

    it('returns false for published photo with future date', function (): void {
        $photo = Photo::factory()->create([
            'status' => Status::Published,
            'published_at' => now()->addDay(),
        ]);

        expect($photo->isPublic())->toBeFalse();
    });

    it('returns false when published_at is null', function (): void {
        $photo = Photo::factory()->create([
            'status' => Status::Published,
            'published_at' => null,
        ]);

        expect($photo->isPublic())->toBeFalse();
    });
});

describe('photo meta field', function (): void {
    it('stores array data in meta', function (): void {
        $meta = [
            'external_url' => 'https://example.com/image.jpg',
            'camera_model' => 'Canon EOS R5',
            'iso' => 400,
        ];

        $photo = Photo::factory()->create(['meta' => $meta]);

        expect($photo->meta)->toBe($meta);
        expect($photo->meta['camera_model'])->toBe('Canon EOS R5');
        expect($photo->meta['iso'])->toBe(400);
    });

    it('handles empty meta array', function (): void {
        $photo = Photo::factory()->create(['meta' => []]);

        expect($photo->meta)->toBe([]);
    });
});

describe('photo timestamps', function (): void {
    it('casts published_at to immutable datetime', function (): void {
        $photo = Photo::factory()->published()->create();

        expect($photo->published_at)->toBeInstanceOf(\DateTimeImmutable::class);
    });

    it('casts taken_at to immutable datetime', function (): void {
        $photo = Photo::factory()->create(['taken_at' => now()]);

        expect($photo->taken_at)->toBeInstanceOf(\DateTimeImmutable::class);
    });

    it('allows null taken_at', function (): void {
        $photo = Photo::factory()->create(['taken_at' => null]);

        expect($photo->taken_at)->toBeNull();
    });
});
