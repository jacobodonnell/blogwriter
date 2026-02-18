<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Storage::fake('public');
    Storage::fake('private');
});

it('serves private media to authenticated users', function (): void {
    $photo = Photo::factory()->draft()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('draft-photo.jpg');
    $photo->addMedia($file)->toMediaCollection('image', 'private');

    $media = $photo->getFirstMedia('image');

    $this->actingAs($this->user)
        ->get(route('admin.media.show', ['media' => $media->id]))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

it('returns 404 for public disk media through the controller', function (): void {
    $photo = Photo::factory()->published()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('public-photo.jpg');
    $photo->addMedia($file)->toMediaCollection('image', 'public');

    $media = $photo->getFirstMedia('image');

    $this->actingAs($this->user)
        ->get(route('admin.media.show', ['media' => $media->id]))
        ->assertNotFound();
});

it('requires authentication to access private media', function (): void {
    $photo = Photo::factory()->draft()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('draft-photo.jpg');
    $photo->addMedia($file)->toMediaCollection('image', 'private');

    $media = $photo->getFirstMedia('image');

    $this->get(route('admin.media.show', ['media' => $media->id]))
        ->assertRedirect();
});

it('serves media conversions', function (): void {
    $photo = Photo::factory()->draft()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('draft-photo.jpg', 1200, 800);
    $photo->addMedia($file)->toMediaCollection('image', 'private');

    $media = $photo->getFirstMedia('image');

    $this->actingAs($this->user)
        ->get(route('admin.media.show', ['media' => $media->id, 'conversion' => 'large']))
        ->assertOk();
});
