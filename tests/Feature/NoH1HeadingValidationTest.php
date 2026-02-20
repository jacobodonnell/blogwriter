<?php

use App\Enums\Status;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('rejects content with H1 heading', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => "# This is H1\n\nSome text",
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});

it('allows content with H2 heading', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Article H2',
        'slug' => 'test-article-h2',
        'content' => "## This is H2\n\nSome text",
        'status' => Status::Draft->value,
    ])->assertSessionDoesntHaveErrors('content');
});

it('allows content with H3 heading', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Article H3',
        'slug' => 'test-article-h3',
        'content' => "### This is H3\n\nSome text",
        'status' => Status::Draft->value,
    ])->assertSessionDoesntHaveErrors('content');
});

it('allows hashtag without space (not a heading)', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Hashtag',
        'slug' => 'test-hashtag',
        'content' => '#hashtag is fine',
        'status' => Status::Draft->value,
    ])->assertSessionDoesntHaveErrors('content');
});

it('rejects null content as required', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Null',
        'slug' => 'test-null',
        'content' => null,
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});

it('detects multiple H1 lines', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'Test Multi H1',
        'slug' => 'test-multi-h1',
        'content' => "## Valid\n\n# Bad Heading\n\nText\n\n# Another Bad",
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});
