<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->appName = config('app.name', 'BlogWriter');
});

it('renders admin dashboard with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard - '.$this->appName.'</title>', false);
});

it('renders admin articles index with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee('<title>Articles - '.$this->appName.'</title>', false);
});

it('renders admin articles create with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.create'))
        ->assertOk()
        ->assertSee('<title>New Article - '.$this->appName.'</title>', false);
});

it('renders admin articles edit with correct title tag', function (): void {
    $article = Article::factory()->for($this->user)->create(['title' => 'My Test Article']);

    $this->actingAs($this->user)
        ->get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('<title>Edit: My Test Article - '.$this->appName.'</title>', false);
});

it('renders admin photos index with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.photos.index'))
        ->assertOk()
        ->assertSee('<title>Photos - '.$this->appName.'</title>', false);
});

it('renders admin photos create with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.photos.create'))
        ->assertOk()
        ->assertSee('<title>New Photo - '.$this->appName.'</title>', false);
});

it('renders admin photos show with correct title tag', function (): void {
    $photo = Photo::factory()->for($this->user)->create(['alt_text' => 'Sunset over mountains']);

    $this->actingAs($this->user)
        ->get(route('admin.photos.show', $photo))
        ->assertOk()
        ->assertSee('<title>Photo: Sunset over mountains - '.$this->appName.'</title>', false);
});

it('renders admin photos edit with correct title tag', function (): void {
    $photo = Photo::factory()->for($this->user)->create();

    $this->actingAs($this->user)
        ->get(route('admin.photos.edit', $photo))
        ->assertOk()
        ->assertSee('<title>Edit Photo - '.$this->appName.'</title>', false);
});

it('renders admin categories index with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('<title>Categories - '.$this->appName.'</title>', false);
});

it('renders admin categories edit with correct title tag', function (): void {
    $category = Category::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.categories.edit', $category))
        ->assertOk()
        ->assertSee('<title>Edit Category - '.$this->appName.'</title>', false);
});

it('renders admin settings profile with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.profile'))
        ->assertOk()
        ->assertSee('<title>Settings - '.$this->appName.'</title>', false);
});

it('renders admin settings site with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.site'))
        ->assertOk()
        ->assertSee('<title>Settings - '.$this->appName.'</title>', false);
});

it('renders admin settings appearance with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.settings.appearance'))
        ->assertOk()
        ->assertSee('<title>Settings - '.$this->appName.'</title>', false);
});
