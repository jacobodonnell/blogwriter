<?php

use App\Models\Photo;

it('displays photo gallery with only published photos', function (): void {
    $publishedPhoto = Photo::factory()->published()->create(['alt_text' => 'Published Photo Alt']);
    $draftPhoto = Photo::factory()->draft()->create(['alt_text' => 'Draft Photo Alt']);

    $response = $this->get(route('photos.index'));

    $response->assertSuccessful();
    $response->assertSee('Published Photo Alt');
    $response->assertDontSee('Draft Photo Alt');
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
