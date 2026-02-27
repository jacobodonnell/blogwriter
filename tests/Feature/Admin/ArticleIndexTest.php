<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('displays the articles index page', function (): void {
    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
    $response->assertSee('Articles');
});

it('defaults to updated_at desc sorting', function (): void {
    $old = Article::factory()->create(['title' => 'Old Article']);
    $old->update(['updated_at' => now()->subDays(5)]);

    $new = Article::factory()->create(['title' => 'New Article']);
    $new->update(['updated_at' => now()]);

    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['New Article', 'Old Article']);
});

it('sorts by title ascending', function (): void {
    Article::factory()->create(['title' => 'Banana Article']);
    Article::factory()->create(['title' => 'Apple Article']);

    $response = $this->get(route('admin.articles.index', ['sort' => 'title', 'direction' => 'asc']));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Apple Article', 'Banana Article']);
});

it('sorts by title descending', function (): void {
    Article::factory()->create(['title' => 'Banana Article']);
    Article::factory()->create(['title' => 'Apple Article']);

    $response = $this->get(route('admin.articles.index', ['sort' => 'title', 'direction' => 'desc']));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Banana Article', 'Apple Article']);
});

it('sorts by status', function (): void {
    Article::factory()->draft()->create(['title' => 'Draft One']);
    Article::factory()->published()->create(['title' => 'Published One']);

    $response = $this->get(route('admin.articles.index', ['sort' => 'status', 'direction' => 'asc']));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Draft One', 'Published One']);
});

it('sorts by published_at', function (): void {
    Article::factory()->published()->create([
        'title' => 'Earlier Post',
        'published_at' => now()->subDays(10),
    ]);
    Article::factory()->published()->create([
        'title' => 'Later Post',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('admin.articles.index', ['sort' => 'published_at', 'direction' => 'desc']));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Later Post', 'Earlier Post']);
});

it('sorts by created_at', function (): void {
    $first = Article::factory()->create(['title' => 'First Created']);
    $first->update(['created_at' => now()->subDays(5)]);

    $second = Article::factory()->create(['title' => 'Second Created']);
    $second->update(['created_at' => now()]);

    $response = $this->get(route('admin.articles.index', ['sort' => 'created_at', 'direction' => 'asc']));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['First Created', 'Second Created']);
});

it('falls back to defaults for invalid sort column and direction', function (): void {
    Article::factory()->create(['title' => 'Test Article']);

    $response = $this->get(route('admin.articles.index', ['sort' => 'invalid_column', 'direction' => 'invalid']));

    $response->assertSuccessful();
    $response->assertViewHas('currentSort', 'updated_at');
    $response->assertViewHas('currentDirection', 'desc');
});

it('searches articles by title', function (): void {
    Article::factory()->create(['title' => 'Laravel Tips and Tricks']);
    Article::factory()->create(['title' => 'React Basics']);

    $response = $this->get(route('admin.articles.index', ['search' => 'Laravel']));

    $response->assertSuccessful();
    $response->assertSee('Laravel Tips and Tricks');
    $response->assertDontSee('React Basics');
});

it('searches articles by slug', function (): void {
    Article::factory()->create(['title' => 'My Great Post', 'slug' => 'my-great-post']);
    Article::factory()->create(['title' => 'Other Post', 'slug' => 'other-post']);

    $response = $this->get(route('admin.articles.index', ['search' => 'great']));

    $response->assertSuccessful();
    $response->assertSee('My Great Post');
    $response->assertDontSee('Other Post');
});

it('combines search with category and status filters', function (): void {
    $category = Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);

    $match = Article::factory()->published()->create([
        'title' => 'Laravel Guide',
        'category_id' => $category->id,
    ]);

    $noCategory = Article::factory()->published()->create(['title' => 'Laravel Basics']);
    $wrongStatus = Article::factory()->draft()->create([
        'title' => 'Laravel Draft',
        'category_id' => $category->id,
    ]);

    $response = $this->get(route('admin.articles.index', [
        'search' => 'Laravel',
        'category' => $category->slug,
        'status' => Status::Public->value,
    ]));

    $response->assertSuccessful();
    $response->assertSee('Laravel Guide');
    $response->assertDontSee('Laravel Basics');
    $response->assertDontSee('Laravel Draft');
});

it('combines sort with filters', function (): void {
    Article::factory()->published()->create([
        'title' => 'Zebra Article',
        'slug' => 'zebra-article',
    ]);
    Article::factory()->published()->create([
        'title' => 'Alpha Article',
        'slug' => 'alpha-article',
    ]);
    Article::factory()->draft()->create(['title' => 'Draft Article']);

    $response = $this->get(route('admin.articles.index', [
        'status' => Status::Public->value,
        'sort' => 'title',
        'direction' => 'asc',
    ]));

    $response->assertSuccessful();
    $response->assertSeeInOrder(['Alpha Article', 'Zebra Article']);
    $response->assertDontSee('Draft Article');
});

it('eager loads featuredPhoto relationship to prevent N+1', function (): void {
    $photo = Photo::factory()->published()->create();
    Article::factory()->create(['photo_id' => $photo->id]);

    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
});

it('displays articles with external featured image URL', function (): void {
    Article::factory()->create([
        'title' => 'External Image Article',
        'external_featured_img_url' => 'https://example.com/image.jpg',
    ]);

    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
    $response->assertSee('External Image Article');
});

it('returns only the table partial for AJAX requests', function (): void {
    Article::factory()->create(['title' => 'AJAX Article']);

    $response = $this->get(
        route('admin.articles.index'),
        ['X-Alpine-Target' => 'articles-table']
    );

    $response->assertSuccessful();
    $response->assertSee('AJAX Article');
    $response->assertSee('id="articles-table"', false);
    $response->assertDontSee('<h1', false);
});

it('returns full page for non-AJAX requests', function (): void {
    Article::factory()->create(['title' => 'Full Page Article']);

    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
    $response->assertSee('Full Page Article');
    $response->assertSee('Articles');
    $response->assertSee('id="articles-table"', false);
});

it('passes currentSort and currentDirection to the view', function (): void {
    $response = $this->get(route('admin.articles.index', ['sort' => 'title', 'direction' => 'asc']));

    $response->assertSuccessful();
    $response->assertViewHas('currentSort', 'title');
    $response->assertViewHas('currentDirection', 'asc');
});

it('preserves query string in pagination', function (): void {
    Article::factory()->published()->count(25)->create();

    $response = $this->get(route('admin.articles.index', [
        'status' => Status::Public->value,
        'sort' => 'title',
        'direction' => 'asc',
    ]));

    $response->assertSuccessful();
    $response->assertSee('sort=title');
    $response->assertSee('direction=asc');
    $response->assertSee('status=public');
});

it('paginates with custom per page value', function (): void {
    Article::factory()->count(15)->create();

    $response = $this->get(route('admin.articles.index', ['perPage' => 10]));

    $response->assertSuccessful();
    $response->assertViewHas('perPage', 10);
    $response->assertViewHas('articles', fn ($articles) => $articles->count() === 10);
});

it('defaults to 20 per page and falls back for invalid values', function (): void {
    Article::factory()->count(25)->create();

    $response = $this->get(route('admin.articles.index'));
    $response->assertSuccessful();
    $response->assertViewHas('perPage', 20);
    $response->assertViewHas('articles', fn ($articles) => $articles->count() === 20);

    $response = $this->get(route('admin.articles.index', ['perPage' => 999]));
    $response->assertSuccessful();
    $response->assertViewHas('perPage', 20);
});
