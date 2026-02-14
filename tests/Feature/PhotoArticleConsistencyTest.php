<?php

use App\Models\Article;
use App\Models\Photo;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('rejects a draft photo when storing an article', function (): void {
    $draftPhoto = Photo::factory()->draft()->create();

    $response = $this->actingAs($this->user)
        ->post('/admin/articles', [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Some content',
            'status' => 'draft',
            'photo_id' => $draftPhoto->id,
        ]);

    $response->assertSessionHasErrors('photo_id');
});

it('rejects a draft photo when updating an article', function (): void {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);
    $draftPhoto = Photo::factory()->draft()->create();

    $response = $this->actingAs($this->user)
        ->put("/admin/articles/{$article->id}", [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'draft',
            'photo_id' => $draftPhoto->id,
        ]);

    $response->assertSessionHasErrors('photo_id');
});

it('accepts a published photo when storing an article', function (): void {
    $publishedPhoto = Photo::factory()->published()->create();

    $response = $this->actingAs($this->user)
        ->post('/admin/articles', [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'Some content',
            'status' => 'draft',
            'photo_id' => $publishedPhoto->id,
        ]);

    $response->assertSessionDoesntHaveErrors('photo_id');
});

it('accepts a published photo when updating an article', function (): void {
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);
    $publishedPhoto = Photo::factory()->published()->create();

    $response = $this->actingAs($this->user)
        ->put("/admin/articles/{$article->id}", [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'draft',
            'photo_id' => $publishedPhoto->id,
        ]);

    $response->assertSessionDoesntHaveErrors('photo_id');
});

it('only shows published photos on the article edit page', function (): void {
    $publishedPhoto = Photo::factory()->published()->create(['alt_text' => 'Visible Photo']);
    $draftPhoto = Photo::factory()->draft()->create(['alt_text' => 'Hidden Draft Photo']);
    $article = Article::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user)
        ->get("/admin/articles/{$article->id}/edit");

    $response->assertOk();
    $response->assertSee('Visible Photo');
    $response->assertDontSee('Hidden Draft Photo');
});

it('allows re-attaching a photo after publish → draft → republish cycle', function (): void {
    $photo = Photo::factory()->published()->create();
    $article = Article::factory()->published()->create([
        'user_id' => $this->user->id,
        'photo_id' => $photo->id,
    ]);

    // Photo goes to draft — observer detaches it
    $photo->update(['status' => 'draft', 'published_at' => null]);
    $article->refresh();
    expect($article->photo_id)->toBeNull();

    // Republish the photo
    $photo->update(['status' => 'published', 'published_at' => now()]);

    // Re-attach via update
    $response = $this->actingAs($this->user)
        ->put("/admin/articles/{$article->id}", [
            'title' => $article->title,
            'slug' => $article->slug,
            'content' => $article->content,
            'status' => 'published',
            'photo_id' => $photo->id,
        ]);

    $response->assertSessionDoesntHaveErrors('photo_id');
    $article->refresh();
    expect($article->photo_id)->toBe($photo->id);
});
