<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

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

it('redirects old slug to current slug with 301', function (): void {
    $photo = Photo::factory()->published()->create([
        'slug' => 'new-slug',
        'past_slugs' => ['old-slug'],
    ]);

    $response = $this->get(route('photos.show', 'old-slug'));

    $response->assertRedirectToRoute('photos.show', 'new-slug');
    expect($response->getStatusCode())->toBe(301);
});

it('returns 404 for old slug of draft photo', function (): void {
    Photo::factory()->draft()->create([
        'slug' => 'draft-current',
        'past_slugs' => ['draft-old'],
    ]);

    $response = $this->get(route('photos.show', 'draft-old'));

    $response->assertNotFound();
});

it('filters photos by search on alt_text', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'Sunset over mountains']);
    Photo::factory()->published()->create(['alt_text' => 'City skyline at night']);

    $this->get('/photos?search=Sunset')
        ->assertSuccessful()
        ->assertSee('Sunset over mountains')
        ->assertDontSee('City skyline at night');
});

it('filters photos by search on caption', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'Photo A', 'caption' => 'Beautiful sunrise']);
    Photo::factory()->published()->create(['alt_text' => 'Photo B', 'caption' => 'Dark alley']);

    $this->get('/photos?search=sunrise')
        ->assertSuccessful()
        ->assertSee('Photo A')
        ->assertDontSee('Photo B');
});

it('filters photos by category', function (): void {
    $category = Category::factory()->create(['name' => 'Nature']);
    Photo::factory()->published()->create(['alt_text' => 'Nature Photo', 'category_id' => $category->id]);
    Photo::factory()->published()->create(['alt_text' => 'Urban Photo']);

    $this->get('/photos?category='.$category->slug)
        ->assertSuccessful()
        ->assertSee('Nature Photo')
        ->assertDontSee('Urban Photo');
});

it('filters photos by status for authenticated users', function (): void {
    $user = User::factory()->create();
    Photo::factory()->published()->create(['alt_text' => 'Published Snap']);
    Photo::factory()->draft()->create(['alt_text' => 'Draft Snap']);

    $this->actingAs($user)
        ->get('/photos?status=draft')
        ->assertSuccessful()
        ->assertSee('Draft Snap')
        ->assertDontSee('Published Snap');
});

it('authenticated user sees only published photos by default', function (): void {
    $user = User::factory()->create();
    Photo::factory()->published()->create(['alt_text' => 'Published Default']);
    Photo::factory()->draft()->create(['alt_text' => 'Draft Hidden']);

    $this->actingAs($user)
        ->get('/photos')
        ->assertSuccessful()
        ->assertSee('Published Default')
        ->assertDontSee('Draft Hidden');
});

it('authenticated user sees only published photos when status is cleared', function (): void {
    $user = User::factory()->create();
    Photo::factory()->published()->create(['alt_text' => 'Published Snap']);
    Photo::factory()->draft()->create(['alt_text' => 'Draft Snap']);

    $this->actingAs($user)
        ->get('/photos?status=')
        ->assertSuccessful()
        ->assertSee('Published Snap')
        ->assertDontSee('Draft Snap');
});

it('guests cannot see draft photos via status filter', function (): void {
    Photo::factory()->published()->create(['alt_text' => 'Public Photo']);
    Photo::factory()->draft()->create(['alt_text' => 'Hidden Draft Photo']);

    $this->get('/photos?status=draft')
        ->assertSuccessful()
        ->assertDontSee('Hidden Draft Photo');
});

it('passes categories to view for all users', function (): void {
    Category::factory()->create(['name' => 'Landscapes']);

    $this->get('/photos')
        ->assertSuccessful()
        ->assertViewHas('categories', fn ($categories) => $categories->isNotEmpty());
});
