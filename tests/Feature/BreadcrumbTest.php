<?php

it('articles index has breadcrumbs', function (): void {
    $response = $this->get(route('articles.index'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('breadcrumbs');
    expect($content)->toContain('Home');
    expect($content)->toContain('Articles');
});

it('photos index has breadcrumbs', function (): void {
    $response = $this->get(route('photos.index'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('breadcrumbs');
    expect($content)->toContain('Home');
    expect($content)->toContain('Photos');
});

it('categories index has breadcrumbs', function (): void {
    $response = $this->get(route('categories.index'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('breadcrumbs');
    expect($content)->toContain('Home');
    expect($content)->toContain('Categories');
});
