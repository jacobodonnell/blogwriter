<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('private');
});

it('sets published_at when publishing a photo for the first time', function (): void {
    $photo = Photo::factory()->draft()->create(['published_at' => null]);

    $photo->update(['status' => Status::Public]);

    expect($photo->fresh()->published_at)->not->toBeNull();
});

it('keeps existing published_at when photo is already published', function (): void {
    $existingDate = now()->subDays(5);
    $photo = Photo::factory()->published()->create(['published_at' => $existingDate]);

    $photo->update(['status' => Status::Public]);

    expect($photo->fresh()->published_at->toDateTimeString())
        ->toBe($existingDate->toDateTimeString());
});

it('clears published_at when unpublishing a photo', function (): void {
    $photo = Photo::factory()->published()->create(['published_at' => now()]);

    $photo->update(['status' => Status::Private]);

    expect($photo->fresh()->published_at)->toBeNull();
});
