<?php

use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('displays photo gallery with only published photos', function (): void {
    $publishedPhoto = Photo::factory()->published()->create(['caption' => 'Published Photo Caption']);
    $draftPhoto = Photo::factory()->draft()->create(['caption' => 'Draft Photo Caption']);

    $response = $this->get(route('photos.index'));

    $response->assertSuccessful();
    $response->assertSee('Published Photo Caption');
    $response->assertDontSee('Draft Photo Caption');
});

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

it('isPublic method returns correct visibility status', function (): void {
    $publishedPhoto = Photo::factory()->published()->create([
        'published_at' => now()->subDay(),
    ]);
    $draftPhoto = Photo::factory()->draft()->create();
    $futurePhoto = Photo::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    expect($publishedPhoto->isPublic())->toBeTrue();
    expect($draftPhoto->isPublic())->toBeFalse();
    expect($futurePhoto->isPublic())->toBeFalse();
});
