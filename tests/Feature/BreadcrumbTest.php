<?php

use App\Models\Article;
use App\Models\Photo;

it('home page has breadcrumbs', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home');
});

it('articles index has breadcrumbs', function (): void {
    $this->get(route('articles.index'))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('Articles');
});

it('article show has breadcrumbs', function (): void {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article->slug))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('Articles')
        ->assertSee($article->title);
});

it('photos index has breadcrumbs', function (): void {
    $this->get(route('photos.index'))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('Photos');
});

it('photo show has breadcrumbs', function (): void {
    $photo = Photo::factory()->published()->create();

    $this->get(route('photos.show', $photo->slug))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('Photos');
});

it('categories index has breadcrumbs', function (): void {
    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('Categories');
});

it('about page has breadcrumbs', function (): void {
    \App\Models\User::factory()->create();

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('breadcrumbs', escape: false)
        ->assertSee('Home')
        ->assertSee('About');
});
