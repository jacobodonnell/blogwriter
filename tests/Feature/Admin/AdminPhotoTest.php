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
        'status' => Status::Published->value,
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
        'status' => Status::Published->value,
        'image' => $file,
    ]);

    $response->assertSessionHasErrors();
    expect(Photo::count())->toBe(1);
});

it('detaches photo from articles when switching to draft', function (): void {
    $photo = Photo::factory()->published()->create();
    $article1 = Article::factory()->published()->create(['photo_id' => $photo->id]);
    $article2 = Article::factory()->published()->create(['photo_id' => $photo->id]);

    $this->put(route('admin.photos.update', $photo), [
        'alt_text' => $photo->alt_text,
        'status' => Status::Draft->value,
    ]);

    expect($article1->fresh()->photo_id)->toBeNull();
    expect($article2->fresh()->photo_id)->toBeNull();
});

it('does not detach articles when photo stays published', function (): void {
    $photo = Photo::factory()->published()->create();
    $article = Article::factory()->published()->create(['photo_id' => $photo->id]);

    $this->put(route('admin.photos.update', $photo), [
        'alt_text' => 'Updated alt text',
        'status' => Status::Published->value,
    ]);

    expect($article->fresh()->photo_id)->toBe($photo->id);
});

it('does not detach articles when publishing a draft photo', function (): void {
    $photo = Photo::factory()->draft()->create();
    $article = Article::factory()->create(['photo_id' => $photo->id]);

    $this->put(route('admin.photos.update', $photo), [
        'alt_text' => $photo->alt_text,
        'status' => Status::Published->value,
    ]);

    expect($article->fresh()->photo_id)->toBe($photo->id);
});

it('sets published_at when uploading a published photo', function (): void {
    $file = UploadedFile::fake()->image('published-photo.jpg');

    $this->post(route('admin.photos.store'), [
        'alt_text' => 'Test photo',
        'status' => Status::Published->value,
        'image_file' => $file,
    ]);

    $photo = Photo::where('filename', 'published-photo.jpg')->firstOrFail();
    expect($photo->published_at)->not->toBeNull();
});

it('leaves published_at null when uploading a draft photo', function (): void {
    $file = UploadedFile::fake()->image('draft-photo.jpg');

    $this->post(route('admin.photos.store'), [
        'alt_text' => 'Draft photo',
        'status' => Status::Draft->value,
        'image_file' => $file,
    ]);

    $photo = Photo::where('filename', 'draft-photo.jpg')->firstOrFail();
    expect($photo->published_at)->toBeNull();
});

it('updating slug populates past_slugs', function (): void {
    $photo = Photo::factory()->published()->create(['slug' => 'old-slug', 'past_slugs' => []]);

    $this->put(route('admin.photos.update', $photo), [
        'slug' => 'new-slug',
        'alt_text' => $photo->alt_text,
        'status' => Status::Published->value,
    ]);

    $photo->refresh();
    expect($photo->slug)->toBe('new-slug')
        ->and($photo->past_slugs)->toContain('old-slug');
});

it('allows a photo to keep its own slug on update', function (): void {
    $photo = Photo::factory()->published()->create(['slug' => 'my-slug']);

    $response = $this->put(route('admin.photos.update', $photo), [
        'slug' => 'my-slug',
        'alt_text' => $photo->alt_text,
        'status' => Status::Published->value,
    ]);

    $response->assertRedirect(route('admin.photos.edit', $photo));
    $response->assertSessionHasNoErrors();
});

it('rejects slug that belongs to another photo on update', function (): void {
    Photo::factory()->create(['slug' => 'taken-slug']);
    $photo = Photo::factory()->published()->create(['slug' => 'my-slug']);

    $response = $this->put(route('admin.photos.update', $photo), [
        'slug' => 'taken-slug',
        'alt_text' => $photo->alt_text,
        'status' => Status::Published->value,
    ]);

    $response->assertSessionHasErrors('slug');
});

it('rejects slugs with uppercase letters', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $response = $this->post(route('admin.photos.store'), [
        'slug' => 'My-Photo',
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
        'image_file' => $file,
    ]);

    $response->assertSessionHasErrors('slug');
});

it('rejects slugs with spaces', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $response = $this->post(route('admin.photos.store'), [
        'slug' => 'bad slug',
        'alt_text' => 'Test',
        'status' => Status::Draft->value,
        'image_file' => $file,
    ]);

    $response->assertSessionHasErrors('slug');
});

it('passes articleCount to the edit view', function (): void {
    $photo = Photo::factory()->published()->create();
    Article::factory()->published()->create(['photo_id' => $photo->id]);
    Article::factory()->published()->create(['photo_id' => $photo->id]);

    $response = $this->get(route('admin.photos.edit', $photo));

    $response->assertSuccessful();
    $response->assertViewHas('articleCount', 2);
});
