<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('does not open View Articles link in a new tab', function (): void {
    $response = $this->get(route('admin.articles.index'));

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*>.*?View Articles.*?<\/a>/s', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->not->toContain('target="_blank"');
});

it('does not open View Photos link in a new tab', function (): void {
    $response = $this->get(route('admin.photos.index'));

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*>.*?View Photos.*?<\/a>/s', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->not->toContain('target="_blank"');
});

it('does not open View Category links in a new tab', function (): void {
    Category::factory()->create(['name' => 'Technology']);

    $response = $this->get(route('admin.categories.index'));

    $response->assertSuccessful();
    $content = $response->getContent();

    preg_match('/<a[^>]*title="View category"[^>]*>/', $content, $matches);
    expect($matches)->not->toBeEmpty();
    expect($matches[0])->not->toContain('target="_blank"');
});

it('uses ph-eye icon instead of ph-arrow-square-out for internal view links', function (): void {
    Category::factory()->create(['name' => 'Tech']);

    $articlesContent = $this->get(route('admin.articles.index'))->getContent();
    $photosContent = $this->get(route('admin.photos.index'))->getContent();
    $categoriesContent = $this->get(route('admin.categories.index'))->getContent();

    preg_match('/<a[^>]*>.*?View Articles.*?<\/a>/s', $articlesContent, $articlesMatch);
    preg_match('/<a[^>]*>.*?View Photos.*?<\/a>/s', $photosContent, $photosMatch);
    preg_match('/<a[^>]*title="View category".*?<\/a>/s', $categoriesContent, $categoriesMatch);

    expect($articlesMatch[0])->toContain('ph-eye')->not->toContain('ph-arrow-square-out');
    expect($photosMatch[0])->toContain('ph-eye')->not->toContain('ph-arrow-square-out');
    expect($categoriesMatch[0])->toContain('ph-eye')->not->toContain('ph-arrow-square-out');
});
