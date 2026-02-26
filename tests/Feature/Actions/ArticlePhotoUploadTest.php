<?php

declare(strict_types=1);

use App\Actions\Photos\HandleArticlePhotoUploadAction;
use App\Enums\Status;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
 * HandleArticlePhotoUploadAction and CreatePhotoFromUploadAction are both
 * final readonly, so they cannot be mocked or extended. These tests use
 * real integration calls instead of simulating failure paths.
 * If you need to test PhotoUploadFailedException handling in the controller,
 * you would need to remove the final keyword or introduce an interface.
 */

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('private');
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('returns photo ID on successful upload', function (): void {
    $action = app(HandleArticlePhotoUploadAction::class);
    $file = UploadedFile::fake()->image('featured.jpg');

    $photoId = $action->handle($file, [
        'title' => 'My Article',
        'slug' => 'my-article',
    ]);

    expect($photoId)->toBeInt()->toBeGreaterThan(0);
    expect(Photo::find($photoId))->not->toBeNull();
});

it('creates photo with published status', function (): void {
    $action = app(HandleArticlePhotoUploadAction::class);
    $file = UploadedFile::fake()->image('featured.jpg');

    $photoId = $action->handle($file, [
        'title' => 'My Article',
        'slug' => 'my-article',
    ]);

    $photo = Photo::find($photoId);
    expect($photo->status)->toBe(Status::Published);
});

it('uses article title as alt text fallback', function (): void {
    $action = app(HandleArticlePhotoUploadAction::class);
    $file = UploadedFile::fake()->image('featured.jpg');

    $photoId = $action->handle($file, [
        'title' => 'My Great Article',
        'slug' => 'my-great-article',
    ]);

    $photo = Photo::find($photoId);
    expect($photo->alt_text)->toBe('My Great Article');
});
