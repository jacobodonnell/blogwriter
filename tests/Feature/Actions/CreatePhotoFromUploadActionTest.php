<?php

declare(strict_types=1);

use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Enums\Status;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('private');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->action = app(CreatePhotoFromUploadAction::class);
});

it('sets published_at when status is published', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Test photo',
        'status' => 'public',
    ]);

    expect($photo->published_at)->not->toBeNull()
        ->and($photo->status)->toBe(Status::Public);
});

it('leaves published_at null when status is draft', function (): void {
    $file = UploadedFile::fake()->image('test.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Draft photo',
        'status' => 'private',
    ]);

    expect($photo->published_at)->toBeNull()
        ->and($photo->status)->toBe(Status::Private);
});

it('stores media on public disk for published photos', function (): void {
    $file = UploadedFile::fake()->image('public.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Public photo',
        'status' => 'public',
    ]);

    expect($photo->getFirstMedia('image')?->disk)->toBe('public');
});

it('stores media on private disk for draft photos', function (): void {
    $file = UploadedFile::fake()->image('private.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Private photo',
        'status' => 'private',
    ]);

    expect($photo->getFirstMedia('image')?->disk)->toBe('private');
});

it('names stored file after slug derived from filename', function (): void {
    $file = UploadedFile::fake()->image('my-cool-photo.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Slug from filename',
        'status' => 'public',
    ]);

    $media = $photo->getFirstMedia('image');
    expect($media?->file_name)->toStartWith($photo->slug.'.');
});

it('uses custom slug when provided and names file after it', function (): void {
    $file = UploadedFile::fake()->image('original-name.jpg');

    $photo = $this->action->handle($file, [
        'slug' => 'custom-slug',
        'alt_text' => 'Custom slug photo',
        'status' => 'public',
    ]);

    expect($photo->slug)->toBe('custom-slug');
    $media = $photo->getFirstMedia('image');
    expect($media?->file_name)->toBe('custom-slug.jpg');
});

it('accepts a Status enum instance for status', function (): void {
    $file = UploadedFile::fake()->image('enum.jpg');

    $photo = $this->action->handle($file, [
        'alt_text' => 'Enum photo',
        'status' => Status::Public,
    ]);

    expect($photo->published_at)->not->toBeNull()
        ->and($photo->status)->toBe(Status::Public);
});
