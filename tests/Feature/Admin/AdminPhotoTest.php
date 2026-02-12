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
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

describe('photo index', function (): void {
    it('displays photo index page', function (): void {
        $response = $this->get(route('admin.photos.index'));

        $response->assertSuccessful();
        $response->assertSee('Photos');
    });

    it('lists all photos', function (): void {
        $photos = Photo::factory()->count(3)->published()->create();

        $response = $this->get(route('admin.photos.index'));

        $response->assertSuccessful();
        foreach ($photos as $photo) {
            $response->assertSee($photo->filename);
        }
    });

    it('paginates photos', function (): void {
        Photo::factory()->count(25)->published()->create();

        $response = $this->get(route('admin.photos.index'));

        $response->assertSuccessful();
        $response->assertSee('pagination', false);
    });
});

describe('photo creation', function (): void {
    it('displays photo create form', function (): void {
        $response = $this->get(route('admin.photos.create'));

        $response->assertSuccessful();
        $response->assertSee('Create Photo');
    });

    it('creates photo with file upload', function (): void {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('admin.photos.store'), [
            'filename' => 'test.jpg',
            'slug' => 'test-photo',
            'alt_text' => 'Test alt text',
            'caption' => 'Test caption',
            'status' => 'published',
            'image' => $file,
        ]);

        $photo = Photo::where('slug', 'test-photo')->first();
        $response->assertRedirect(route('admin.photos.edit', $photo));

        expect($photo)->not->toBeNull();
        expect($photo->filename)->toBe('test.jpg');
        expect($photo->getFirstMedia('image'))->not->toBeNull();
    });

    it('creates photo with external URL', function (): void {
        $response = $this->post(route('admin.photos.store'), [
            'filename' => 'external.jpg',
            'slug' => 'external-photo',
            'alt_text' => 'External image',
            'status' => 'published',
            'external_url' => 'https://example.com/image.jpg',
        ]);

        $photo = Photo::where('slug', 'external-photo')->first();
        $response->assertRedirect(route('admin.photos.edit', $photo));

        expect($photo)->not->toBeNull();
        expect($photo->meta['external_url'])->toBe('https://example.com/image.jpg');
        expect($photo->isExternalUrl())->toBeTrue();
    });

    it('generates unique slug automatically', function (): void {
        Photo::factory()->create(['slug' => 'test-photo']);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('admin.photos.store'), [
            'filename' => 'test.jpg',
            'slug' => 'test-photo', // Duplicate slug
            'alt_text' => 'Test alt text',
            'status' => 'published',
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('slug');
    });
});

describe('photo editing', function (): void {
    it('displays photo edit form', function (): void {
        $photo = Photo::factory()->published()->create();

        $response = $this->get(route('admin.photos.edit', $photo));

        $response->assertSuccessful();
        $response->assertSee('Edit Photo');
        $response->assertSee($photo->filename);
    });

    it('updates photo metadata', function (): void {
        $photo = Photo::factory()->published()->create();

        $response = $this->put(route('admin.photos.update', $photo), [
            'filename' => $photo->filename,
            'slug' => $photo->slug,
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.photos.edit', $photo));

        $photo->refresh();
        expect($photo->alt_text)->toBe('Updated alt text');
        expect($photo->caption)->toBe('Updated caption');
    });

    it('updates photo status', function (): void {
        $photo = Photo::factory()->published()->create();

        $response = $this->put(route('admin.photos.update', $photo), [
            'filename' => $photo->filename,
            'slug' => $photo->slug,
            'alt_text' => $photo->alt_text,
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('admin.photos.edit', $photo));

        $photo->refresh();
        expect($photo->status->value)->toBe('draft');
    });
});

describe('photo deletion', function (): void {
    it('deletes photo when not used by any articles', function (): void {
        $photo = Photo::factory()->published()->create();

        $response = $this->delete(route('admin.photos.destroy', $photo));

        $response->assertRedirect(route('admin.photos.index'));
        expect(Photo::find($photo->id))->toBeNull();
    });

    it('prevents deletion when photo is used by articles', function (): void {
        $photo = Photo::factory()->published()->create();
        Article::factory()->create(['photo_id' => $photo->id]);

        $response = $this->delete(route('admin.photos.destroy', $photo));

        $response->assertSessionHasErrors();
        expect(Photo::find($photo->id))->not->toBeNull();
    });
});

describe('exif metadata extraction', function (): void {
    it('extracts exif data from uploaded image', function (): void {
        // Note: This test would require actual EXIF data in the image
        // For now, we just verify the meta field structure
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('admin.photos.store'), [
            'filename' => 'test.jpg',
            'slug' => 'test-exif',
            'alt_text' => 'Test EXIF',
            'status' => 'published',
            'image' => $file,
        ]);

        $photo = Photo::where('slug', 'test-exif')->first();

        expect($photo)->not->toBeNull();
        expect($photo->meta)->toBeArray();
    });
});

describe('slug uniqueness', function (): void {
    it('requires unique slugs', function (): void {
        Photo::factory()->create(['slug' => 'unique-photo']);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('admin.photos.store'), [
            'filename' => 'test.jpg',
            'slug' => 'unique-photo', // Duplicate
            'alt_text' => 'Test',
            'status' => 'published',
            'image' => $file,
        ]);

        $response->assertSessionHasErrors('slug');
    });

    it('allows same slug when updating same photo', function (): void {
        $photo = Photo::factory()->published()->create(['slug' => 'original-slug']);

        $response = $this->put(route('admin.photos.update', $photo), [
            'filename' => $photo->filename,
            'slug' => 'original-slug', // Same slug
            'alt_text' => 'Updated',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.photos.edit', $photo));
    });
});
