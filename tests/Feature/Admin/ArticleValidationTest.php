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

function validArticleData(array $overrides = []): array
{
    return array_merge([
        'title' => 'My Test Article',
        'slug' => 'my-test-article',
        'content' => 'This is article content for testing.',
        'status' => Status::Draft->value,
    ], $overrides);
}

// --- Store validation ---

it('store rejects title shorter than 3 chars', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'title' => 'ab',
    ]))->assertSessionHasErrors('title');
});

it('store rejects invalid slug format', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'slug' => 'Invalid Slug!',
    ]))->assertSessionHasErrors('slug');
});

it('store rejects duplicate slug', function (): void {
    Article::factory()->create(['slug' => 'taken-slug']);

    $this->post(route('admin.articles.store'), validArticleData([
        'slug' => 'taken-slug',
    ]))->assertSessionHasErrors('slug');
});

it('update allows own slug', function (): void {
    $article = Article::factory()->create(['slug' => 'my-slug']);

    $this->put(route('admin.articles.update', $article), validArticleData([
        'title' => $article->title,
        'slug' => 'my-slug',
        'content' => $article->content,
        'status' => $article->status->value,
    ]))->assertSessionDoesntHaveErrors('slug');
});

it('store rejects invalid status value', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'status' => 'archived',
    ]))->assertSessionHasErrors('status');
});

it('store rejects non-existent category_id', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'category_id' => 99999,
    ]))->assertSessionHasErrors('category_id');
});

it('store rejects draft photo as featured image', function (): void {
    $draftPhoto = Photo::factory()->draft()->create();

    $this->post(route('admin.articles.store'), validArticleData([
        'photo_id' => $draftPhoto->id,
    ]))->assertSessionHasErrors('photo_id');
});

it('store rejects non-URL featured_image string', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'featured_image' => 'not-a-url',
    ]))->assertSessionHasErrors('featured_image');
});

it('store rejects oversized featured_image_file', function (): void {
    $file = UploadedFile::fake()->image('large.jpg')->size(3000);

    $this->post(route('admin.articles.store'), validArticleData([
        'featured_image_file' => $file,
    ]))->assertSessionHasErrors('featured_image_file');
});

// --- Preview validation ---

it('preview accepts minimal data', function (): void {
    $article = Article::factory()->create();

    $this->put(route('admin.articles.preview.update', $article), [
        'title' => '',
    ])->assertSessionHasNoErrors();
});

it('preview enforces max lengths and valid status', function (): void {
    $article = Article::factory()->create();

    $this->put(route('admin.articles.preview.update', $article), [
        'title' => str_repeat('a', 256),
        'status' => 'archived',
    ])->assertSessionHasErrors(['title', 'status']);
});

// --- Mutual exclusivity ---

it('rejects multiple featured image methods', function (): void {
    $photo = Photo::factory()->published()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->post(route('admin.articles.store'), validArticleData([
        'photo_id' => $photo->id,
        'featured_image_file' => $file,
    ]))->assertSessionHasErrors('featured_image');
});
