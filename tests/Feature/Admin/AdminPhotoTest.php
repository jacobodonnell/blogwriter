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

it('displays photo index page', function (): void {
    $response = $this->get(route('admin.photos.index'));

    $response->assertSuccessful();
    $response->assertSee('Photos');
});

it('displays photo create form', function (): void {
    $response = $this->get(route('admin.photos.create'));

    $response->assertSuccessful();
    $response->assertSee('Create Photo');
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

it('prevents deletion when photo is used by articles', function (): void {
    $photo = Photo::factory()->published()->create();
    Article::factory()->create(['photo_id' => $photo->id]);

    $response = $this->delete(route('admin.photos.destroy', $photo));

    $response->assertSessionHasErrors();
    expect(Photo::find($photo->id))->not->toBeNull();
});

it('requires unique slugs', function (): void {
    $existingPhoto = Photo::factory()->create(['slug' => 'unique-photo']);

    $file = UploadedFile::fake()->image('test.jpg');

    $response = $this->post(route('admin.photos.store'), [
        'filename' => 'test.jpg',
        'slug' => 'unique-photo',
        'alt_text' => 'Test',
        'status' => 'published',
        'image' => $file,
    ]);

    $response->assertSessionHasErrors();
    expect(Photo::count())->toBe(1);
});
