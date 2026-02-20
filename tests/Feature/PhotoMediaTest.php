<?php

declare(strict_types=1);

use App\Actions\Photos\CreatePhotoFromUploadAction;
use App\Enums\Status;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

afterEach(function (): void {
    // Clean up media files created during tests
    Photo::all()->each(function (Photo $photo): void {
        $photo->clearMediaCollection('image');
        $photo->delete();
    });
});

it('creates a spatie media entry when image is attached', function (): void {
    $file = UploadedFile::fake()->image('test.jpg', 400, 300);

    $action = app(CreatePhotoFromUploadAction::class);
    $photo = $action->handle($file, [
        'user_id' => $this->user->id,
        'alt_text' => 'Test image',
        'status' => Status::Published,
    ]);

    expect($photo->getFirstMedia('image'))->not->toBeNull()
        ->and($photo->getFirstMedia('image')->file_name)->toBe('test.jpg');
})->group('integration');

it('generates thumbnail, medium, and large conversions', function (): void {
    $file = UploadedFile::fake()->image('test.jpg', 400, 300);

    $action = app(CreatePhotoFromUploadAction::class);
    $photo = $action->handle($file, [
        'user_id' => $this->user->id,
        'alt_text' => 'Test image',
        'status' => Status::Published,
    ]);

    $media = $photo->getFirstMedia('image');

    expect($media->hasGeneratedConversion('thumbnail'))->toBeTrue()
        ->and($media->hasGeneratedConversion('medium'))->toBeTrue()
        ->and($media->hasGeneratedConversion('large'))->toBeTrue();
})->group('integration');

it('stores published photos on public disk', function (): void {
    $file = UploadedFile::fake()->image('test.jpg', 400, 300);

    $action = app(CreatePhotoFromUploadAction::class);
    $photo = $action->handle($file, [
        'user_id' => $this->user->id,
        'alt_text' => 'Test image',
        'status' => Status::Published,
    ]);

    expect($photo->getFirstMedia('image')->disk)->toBe('public');
})->group('integration');

it('stores draft photos on private disk', function (): void {
    $file = UploadedFile::fake()->image('test.jpg', 400, 300);

    $action = app(CreatePhotoFromUploadAction::class);
    $photo = $action->handle($file, [
        'user_id' => $this->user->id,
        'alt_text' => 'Test image',
        'status' => Status::Draft,
    ]);

    expect($photo->getFirstMedia('image')->disk)->toBe('private');
})->group('integration');
