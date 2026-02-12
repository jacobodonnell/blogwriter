<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);
});

it('redirects guest to login when accessing admin dashboard', function (): void {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

it('returns successful response for admin dashboard when authenticated', function (): void {
    $response = $this->actingAs($this->user)->get('/admin');

    $response->assertSuccessful();
});

it('can create and update an article through CRUD endpoints', function (): void {
    $articleData = [
        'title' => 'New Test Article',
        'slug' => 'new-test-article',
        'content' => 'This is the content of the new article.',
        'status' => 'published',
        'published_at' => now()->format('Y-m-d H:i:s'),
    ];

    $response = $this->actingAs($this->user)
        ->post('/admin/articles', $articleData);

    $response->assertRedirect();

    $this->assertDatabaseHas('articles', [
        'title' => 'New Test Article',
        'slug' => 'new-test-article',
        'status' => 'published',
    ]);

    $article = Article::where('slug', 'new-test-article')->first();

    $updateData = [
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'content' => $article->content,
        'status' => 'published',
        'published_at' => $article->published_at?->format('Y-m-d H:i:s'),
    ];

    $response = $this->actingAs($this->user)
        ->put("/admin/articles/{$article->id}", $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
    ]);
});
