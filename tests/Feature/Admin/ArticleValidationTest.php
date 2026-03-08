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
        'status' => Status::Private->value,
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

it('update auto-generates slug from title when slug is empty', function (): void {
    $article = Article::factory()->create(['title' => 'Original Title', 'slug' => 'original-title']);

    $this->put(route('admin.articles.update', $article), validArticleData([
        'title' => 'New Article Title',
        'slug' => '',
        'content' => $article->content,
        'status' => $article->status->value,
    ]))->assertSessionDoesntHaveErrors('slug')
        ->assertRedirect();

    expect($article->fresh()->slug)->toBe('new-article-title');
});

it('update auto-generates unique slug when slug is empty and title conflicts', function (): void {
    Article::factory()->create(['slug' => 'my-title']);
    $article = Article::factory()->create(['title' => 'Old', 'slug' => 'old-slug']);

    $this->put(route('admin.articles.update', $article), validArticleData([
        'title' => 'My Title',
        'slug' => '',
        'content' => $article->content,
        'status' => $article->status->value,
    ]))->assertSessionDoesntHaveErrors('slug')
        ->assertRedirect();

    expect($article->fresh()->slug)->toBe('my-title-1');
});

it('update preserves custom slug when provided', function (): void {
    $article = Article::factory()->create();

    $this->put(route('admin.articles.update', $article), validArticleData([
        'title' => $article->title,
        'slug' => 'custom-slug-value',
        'content' => $article->content,
        'status' => $article->status->value,
    ]))->assertSessionDoesntHaveErrors('slug')
        ->assertRedirect();

    expect($article->fresh()->slug)->toBe('custom-slug-value');
});

it('store auto-generates slug from title when slug is empty', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'title' => 'My Brand New Article',
        'slug' => '',
    ]))->assertRedirect();

    $article = Article::latest('id')->first();
    expect($article->slug)->toBe('my-brand-new-article');
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

// --- CRLF normalization ---

it('store normalizes CRLF to LF in content', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'content' => "Line one\r\nLine two\r\nLine three",
    ]));

    $article = Article::latest('id')->first();

    expect($article->content)->toBe("Line one\nLine two\nLine three");
});

it('update normalizes CRLF to LF in content', function (): void {
    $article = Article::factory()->create();

    $this->put(route('admin.articles.update', $article), validArticleData([
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => "Updated\r\nwith\r\nCRLF",
        'status' => $article->status->value,
    ]));

    expect($article->fresh()->content)->toBe("Updated\nwith\nCRLF");
});

// --- H1 heading validation (server-side safety net) ---

it('store rejects content with H1 markdown heading', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'content' => "# This is an H1 heading\n\nSome content.",
    ]))->assertSessionHasErrors('content');
});

it('store accepts content with H2 heading', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'content' => "## This is an H2 heading\n\nSome content.",
    ]))->assertSessionDoesntHaveErrors('content');
});

it('store accepts content with H3 and deeper headings', function (): void {
    $this->post(route('admin.articles.store'), validArticleData([
        'content' => "### H3\n\n#### H4\n\n##### H5",
    ]))->assertSessionDoesntHaveErrors('content');
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
