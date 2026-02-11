<?php

use App\Models\Article;
use App\Models\Category;
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

it('returns successful response for articles index when authenticated', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/articles');

    $response->assertSuccessful();
});

it('shows articles list on articles index', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Test Article in Admin',
    ]);

    $response = $this->actingAs($this->user)->get('/admin/articles');

    $response->assertSuccessful()
        ->assertSee($article->title);
});

it('returns successful response for articles create page', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/articles/create');

    $response->assertSuccessful();
});

it('stores a new article', function (): void {
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
});

it('returns successful response for articles edit page', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Test Edit Article Title',
    ]);

    $response = $this->actingAs($this->user)
        ->get("/admin/articles/{$article->id}/edit");

    $response->assertSuccessful()
        ->assertSee('Test Edit Article Title');
});

it('updates an existing article', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
    ]);

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

it('deletes an article', function (): void {
    $article = Article::factory()->published()->create();

    $response = $this->actingAs($this->user)
        ->delete("/admin/articles/{$article->id}");

    $response->assertRedirect();

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});

it('returns successful response for categories index', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/categories');

    $response->assertSuccessful();
});

it('shows categories list on categories index', function (): void {
    $category = Category::factory()->create([
        'name' => 'Test Category',
    ]);

    $response = $this->actingAs($this->user)->get('/admin/categories');

    $response->assertSuccessful()
        ->assertSee($category->name);
});

it('stores a new category', function (): void {
    $categoryData = [
        'name' => 'New Test Category',
        'slug' => 'new-test-category',
        'description' => 'This is a test category description.',
    ];

    $response = $this->actingAs($this->user)
        ->post('/admin/categories', $categoryData);

    $response->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'name' => 'New Test Category',
        'slug' => 'new-test-category',
    ]);
});

it('updates an existing category', function (): void {
    $category = Category::factory()->create([
        'name' => 'Original Category',
        'slug' => 'original-category',
    ]);

    $updateData = [
        'name' => 'Updated Category',
        'slug' => 'updated-category',
        'description' => 'Updated description.',
    ];

    $response = $this->actingAs($this->user)
        ->put("/admin/categories/{$category->id}", $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category',
        'slug' => 'updated-category',
    ]);
});

it('deletes a category', function (): void {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->user)
        ->delete("/admin/categories/{$category->id}");

    $response->assertRedirect();

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

it('returns successful response for category edit page', function (): void {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->user)
        ->get("/admin/categories/{$category->id}/edit");

    $response->assertSuccessful()
        ->assertSee($category->name);
});

it('returns successful response for settings page when authenticated', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/settings');

    $response->assertSuccessful()
        ->assertSee('Settings')
        ->assertSee('Configure your BlogWriter site.');
});

it('shows site configuration on settings page', function (): void {
    $response = $this->actingAs($this->user)->get('/admin/settings');

    $response->assertSuccessful()
        ->assertSee('Site Information')
        ->assertSee('Your Profile')
        ->assertSee('Environment');
});
