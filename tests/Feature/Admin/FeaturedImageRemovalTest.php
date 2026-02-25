<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Storage::fake('public');
    Storage::fake('private');
});

it('clears photo_id when remove_featured_image is sent', function (): void {
    $photo = Photo::factory()->published()->for($this->user)->create();
    $article = Article::factory()->draft()->for($this->user)->create([
        'photo_id' => $photo->id,
    ]);

    $this->put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => Status::Draft->value,
        'remove_featured_image' => '1',
    ])->assertRedirect();

    expect($article->fresh()->photo_id)->toBeNull();
});

it('clears external_featured_img_url when remove_featured_image is sent', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'external_featured_img_url' => 'https://example.com/image.jpg',
    ]);

    $this->put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => Status::Draft->value,
        'remove_featured_image' => '1',
    ])->assertRedirect();

    expect($article->fresh()->external_featured_img_url)->toBeNull();
});
