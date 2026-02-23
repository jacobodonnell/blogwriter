<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// --- Article Auth Links ---

it('does not show edit links on article show for guests', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertDontSee(route('admin.articles.edit', $article))
        ->assertDontSee('All Articles');
});

it('shows edit and all articles links on article show for auth users', function (): void {
    $user = User::factory()->create();
    $article = Article::factory()->published()->create();

    $this->actingAs($user)
        ->get(route('articles.show', $article->slug))
        ->assertSuccessful()
        ->assertSee(route('admin.articles.edit', $article))
        ->assertSee('All Articles');
});

it('does not show edit links on article index for guests', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee(route('admin.articles.edit', $article));
});

it('shows edit links on article index for auth users', function (): void {
    $user = User::factory()->create();
    $article = Article::factory()->published()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('admin.articles.edit', $article));
});

// --- Photo Auth Links ---

it('does not show edit links on photo show for guests', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->get(route('photos.show', $photo->slug))
        ->assertSuccessful()
        ->assertDontSee(route('admin.photos.edit', $photo))
        ->assertDontSee('All Photos');
});

it('shows edit and all photos links on photo show for auth users', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->published()->create();

    $this->actingAs($user)
        ->get(route('photos.show', $photo->slug))
        ->assertSuccessful()
        ->assertSee(route('admin.photos.edit', $photo))
        ->assertSee('All Photos');
});

it('does not show edit or upload on photo index for guests', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->get(route('photos.index'))
        ->assertSuccessful()
        ->assertDontSee(route('admin.photos.edit', $photo))
        ->assertDontSee('Upload Photo');
});

it('shows edit and upload photo on photo index for auth users', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->published()->create();

    $this->actingAs($user)
        ->get(route('photos.index'))
        ->assertSuccessful()
        ->assertSee(route('admin.photos.edit', $photo))
        ->assertSee('Upload Photo');
});

// --- Photo Upload via AJAX ---

it('allows auth user to upload a photo via AJAX', function (): void {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('admin.photos.store'), [
            'image_file' => UploadedFile::fake()->image('test-photo.jpg', 800, 600),
            'alt_text' => 'A beautiful sunset',
            'status' => Status::Published->value,
        ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['photo' => ['id', 'image_url', 'alt_text']]);
    $this->assertDatabaseHas('photos', ['alt_text' => 'A beautiful sunset']);
});

it('returns 422 when required fields are missing for photo upload', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('admin.photos.store'), [])
        ->assertUnprocessable();
});

it('prevents guests from uploading photos', function (): void {
    $this->postJson(route('admin.photos.store'), [
        'image_file' => UploadedFile::fake()->image('test-photo.jpg'),
        'alt_text' => 'Test',
        'status' => Status::Published->value,
    ])->assertUnauthorized();
});

// --- About Page Auth Links ---

it('does not show edit profile link on about page for guests', function (): void {
    User::factory()->create();

    $this->get(route('about'))
        ->assertSuccessful()
        ->assertDontSee('Edit Profile');
});

it('shows edit profile link on about page for auth users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('about'))
        ->assertSuccessful()
        ->assertSee('Edit Profile');
});

// --- Article Breadcrumb ---

it('shows Articles link in article breadcrumb instead of category', function (): void {
    $category = Category::factory()->create(['name' => 'Technology']);
    $article = Article::factory()->published()->create(['category_id' => $category->id]);

    $response = $this->get(route('articles.show', $article->slug));

    $response->assertSuccessful();
    $response->assertSee(route('articles.index'));
    $response->assertSee('Articles</a>', false);
});

// --- Photo Categories ---

it('allows auth user to upload a photo with category via AJAX', function (): void {
    Storage::fake('public');
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('admin.photos.store'), [
            'image_file' => UploadedFile::fake()->image('categorized.jpg', 800, 600),
            'alt_text' => 'Categorized photo',
            'status' => Status::Published->value,
            'category_id' => $category->id,
        ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('photos', [
        'alt_text' => 'Categorized photo',
        'category_id' => $category->id,
    ]);
});

it('shows category badge on photo show page', function (): void {
    $category = Category::factory()->create(['name' => 'Landscapes']);
    $photo = Photo::factory()->published()->withCategory($category)->create();

    $this->get(route('photos.show', $photo->slug))
        ->assertSuccessful()
        ->assertSee('Landscapes');
});

it('allows updating photo category', function (): void {
    Storage::fake('public');
    $user = User::factory()->create();
    $photo = Photo::factory()->published()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->put(route('admin.photos.update', $photo), [
            'alt_text' => $photo->alt_text,
            'status' => Status::Published->value,
            'category_id' => $category->id,
        ]);

    expect($photo->fresh()->category_id)->toBe($category->id);
});

it('returns 404 for draft article regardless of auth', function (): void {
    $user = User::factory()->create();
    $article = Article::factory()->draft()->create();

    $this->actingAs($user)
        ->get(route('articles.show', $article->slug))
        ->assertNotFound();
});
