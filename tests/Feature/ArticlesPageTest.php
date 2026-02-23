<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;

it('renders the articles page', function (): void {
    $this->get('/articles')
        ->assertSuccessful()
        ->assertViewIs('public.articles');
});

it('shows published articles', function (): void {
    $article = Article::factory()->published()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertSee($article->title);
});

it('does not show draft articles', function (): void {
    $article = Article::factory()->draft()->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee($article->title);
});

it('paginates articles', function (): void {
    Article::factory()->published()->count(15)->create();

    $this->get('/articles')
        ->assertSuccessful()
        ->assertViewHas('articles', fn ($articles) => $articles->count() === 10);
});

it('shows admin buttons for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee('Manage Articles')
        ->assertSee('New Article');
});

it('hides admin buttons for guests', function (): void {
    $this->get('/articles')
        ->assertSuccessful()
        ->assertDontSee('Manage Articles')
        ->assertDontSee('New Article');
});

it('shows edit button on articles for authenticated users', function (): void {
    $user = User::factory()->create();
    Article::factory()->published()->create();

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee('Edit');
});

it('filters articles by search on title', function (): void {
    Article::factory()->published()->create(['title' => 'Laravel Tips and Tricks']);
    Article::factory()->published()->create(['title' => 'PHP Best Practices']);

    $this->get('/articles?search=Laravel')
        ->assertSuccessful()
        ->assertSee('Laravel Tips and Tricks')
        ->assertDontSee('PHP Best Practices');
});

it('filters articles by search on slug', function (): void {
    Article::factory()->published()->create(['title' => 'My Article', 'slug' => 'laravel-tips']);
    Article::factory()->published()->create(['title' => 'Other Article', 'slug' => 'php-basics']);

    $this->get('/articles?search=laravel-tips')
        ->assertSuccessful()
        ->assertSee('My Article')
        ->assertDontSee('Other Article');
});

it('filters articles by category', function (): void {
    $category = Category::factory()->create(['name' => 'Tech']);
    $article = Article::factory()->published()->create(['title' => 'Tech Article', 'category_id' => $category->id]);
    $other = Article::factory()->published()->create(['title' => 'Uncategorized Article']);

    $this->get('/articles?category='.$category->slug)
        ->assertSuccessful()
        ->assertSee('Tech Article')
        ->assertDontSee('Uncategorized Article');
});

it('filters articles by status for authenticated users', function (): void {
    $user = User::factory()->create();
    Article::factory()->published()->create(['title' => 'Published One']);
    Article::factory()->draft()->create(['title' => 'Draft One']);

    $this->actingAs($user)
        ->get('/articles?status=draft')
        ->assertSuccessful()
        ->assertSee('Draft One')
        ->assertDontSee('Published One');
});

it('authenticated user sees only published articles by default', function (): void {
    $user = User::factory()->create();
    Article::factory()->published()->create(['title' => 'Published Default']);
    Article::factory()->draft()->create(['title' => 'Draft Hidden']);

    $this->actingAs($user)
        ->get('/articles')
        ->assertSuccessful()
        ->assertSee('Published Default')
        ->assertDontSee('Draft Hidden');
});

it('authenticated user sees only published articles when status is cleared', function (): void {
    $user = User::factory()->create();
    Article::factory()->published()->create(['title' => 'Published One']);
    Article::factory()->draft()->create(['title' => 'Draft One']);

    $this->actingAs($user)
        ->get('/articles?status=')
        ->assertSuccessful()
        ->assertSee('Published One')
        ->assertDontSee('Draft One');
});

it('guests cannot see drafts via status filter', function (): void {
    Article::factory()->published()->create(['title' => 'Published Article']);
    Article::factory()->draft()->create(['title' => 'Secret Draft']);

    $this->get('/articles?status=draft')
        ->assertSuccessful()
        ->assertDontSee('Secret Draft');
});

it('preserves query string in pagination', function (): void {
    Article::factory()->published()->count(15)
        ->sequence(fn ($seq) => ['title' => 'Searchable Article '.$seq->index])
        ->create();

    $this->get('/articles?search=Searchable')
        ->assertSuccessful()
        ->assertSee('search=Searchable');
});

it('passes categories to view', function (): void {
    Category::factory()->create(['name' => 'TestCategory']);

    $this->get('/articles')
        ->assertSuccessful()
        ->assertViewHas('categories');
});

it('combines multiple filters', function (): void {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Code']);

    Article::factory()->published()->create(['title' => 'Laravel Code Tips', 'category_id' => $category->id]);
    Article::factory()->published()->create(['title' => 'Laravel Other Tips']);
    Article::factory()->draft()->create(['title' => 'Laravel Code Draft', 'category_id' => $category->id]);

    $this->actingAs($user)
        ->get('/articles?search=Laravel&category='.$category->slug.'&status=published')
        ->assertSuccessful()
        ->assertSee('Laravel Code Tips')
        ->assertDontSee('Laravel Other Tips')
        ->assertDontSee('Laravel Code Draft');
});

it('defaults to newest first', function (): void {
    $old = Article::factory()->published()->create([
        'title' => 'Old Article',
        'published_at' => now()->subDays(10),
    ]);
    $new = Article::factory()->published()->create([
        'title' => 'New Article',
        'published_at' => now(),
    ]);

    $this->get('/articles')
        ->assertSuccessful()
        ->assertSeeInOrder(['New Article', 'Old Article']);
});

it('sorts articles by oldest first', function (): void {
    $old = Article::factory()->published()->create([
        'title' => 'Old Article',
        'published_at' => now()->subDays(10),
    ]);
    $new = Article::factory()->published()->create([
        'title' => 'New Article',
        'published_at' => now(),
    ]);

    $this->get('/articles?sort=oldest')
        ->assertSuccessful()
        ->assertSeeInOrder(['Old Article', 'New Article']);
});

it('sorts articles by title ascending', function (): void {
    Article::factory()->published()->create(['title' => 'Zebra Article', 'published_at' => now()]);
    Article::factory()->published()->create(['title' => 'Alpha Article', 'published_at' => now()]);

    $this->get('/articles?sort=title_asc')
        ->assertSuccessful()
        ->assertSeeInOrder(['Alpha Article', 'Zebra Article']);
});

it('sorts articles by title descending', function (): void {
    Article::factory()->published()->create(['title' => 'Zebra Article', 'published_at' => now()]);
    Article::factory()->published()->create(['title' => 'Alpha Article', 'published_at' => now()]);

    $this->get('/articles?sort=title_desc')
        ->assertSuccessful()
        ->assertSeeInOrder(['Zebra Article', 'Alpha Article']);
});

it('defaults to newest first when sort is invalid', function (): void {
    $old = Article::factory()->published()->create([
        'title' => 'Old Article',
        'published_at' => now()->subDays(10),
    ]);
    $new = Article::factory()->published()->create([
        'title' => 'New Article',
        'published_at' => now(),
    ]);

    $this->get('/articles?sort=bogus')
        ->assertSuccessful()
        ->assertSeeInOrder(['New Article', 'Old Article']);
});

it('preserves sort in pagination query string', function (): void {
    Article::factory()->published()->count(15)
        ->sequence(fn ($seq) => ['title' => 'Sorted Article '.$seq->index])
        ->create();

    $this->get('/articles?sort=oldest')
        ->assertSuccessful()
        ->assertSee('sort=oldest');
});
