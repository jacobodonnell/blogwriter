<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('returns reconstructed revision content as json', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $revision = ArticleRevision::factory()->for($article)->create([
        'title' => 'Rev Title',
        'content' => '# Hello World',
    ]);

    getJson(route('admin.articles.revisions.show', [$article, $revision]))
        ->assertOk()
        ->assertJson([
            'title' => 'Rev Title',
            'content' => '# Hello World',
        ])
        ->assertJsonStructure(['title', 'content', 'created_at']);
});

it('returns 404 for revision belonging to a different article', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $otherArticle = Article::factory()->create(['user_id' => $this->user->id]);
    $revision = ArticleRevision::factory()->for($otherArticle)->create();

    getJson(route('admin.articles.revisions.show', [$article, $revision]))
        ->assertNotFound();
});

it('requires authentication', function (): void {
    auth()->logout();

    $article = Article::factory()->create();
    $revision = ArticleRevision::factory()->for($article)->create();

    getJson(route('admin.articles.revisions.show', [$article, $revision]))
        ->assertUnauthorized();
});

it('reconstructs content through a diff chain', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'content' => 'Version two content.',
    ]);

    // Base revision (full content)
    ArticleRevision::factory()->for($article)->create([
        'title' => 'V1',
        'content' => 'Version one content.',
        'created_at' => now()->subMinutes(2),
    ]);

    // Diff revision
    $revisionService = app(App\Services\RevisionService::class);
    $diff = $revisionService->generateDiff('Version one content.', 'Version two content.');

    $rev2 = ArticleRevision::factory()->for($article)->create([
        'title' => 'V2',
        'content' => $diff,
        'created_at' => now()->subMinute(),
    ]);

    getJson(route('admin.articles.revisions.show', [$article, $rev2]))
        ->assertOk()
        ->assertJson([
            'title' => 'V2',
            'content' => 'Version two content.',
        ]);
});

it('deletes a revision via the destroy endpoint', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);

    $revision = ArticleRevision::factory()->for($article)->create([
        'title' => 'Test',
        'content' => 'Some content',
    ]);

    deleteJson(route('admin.articles.revisions.destroy', [$article, $revision]))
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    expect(ArticleRevision::find($revision->id))->toBeNull();
});

it('returns 404 when deleting revision that does not belong to article', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $otherArticle = Article::factory()->create(['user_id' => $this->user->id]);

    $revision = ArticleRevision::factory()->for($otherArticle)->create();

    deleteJson(route('admin.articles.revisions.destroy', [$article, $revision]))
        ->assertNotFound();
});

it('deletes a middle revision and preserves chain integrity via HTTP', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    $revisionService = app(App\Services\RevisionService::class);

    $v1 = 'First version content';
    $v2 = 'Second version content';
    $v3 = 'Third version content';

    $base = ArticleRevision::factory()->for($article)->create([
        'title' => 'V1',
        'content' => $v1,
    ]);

    $diff1 = ArticleRevision::factory()->for($article)->create([
        'title' => 'V2',
        'content' => $revisionService->generateDiff($v1, $v2),
    ]);

    $diff2 = ArticleRevision::factory()->for($article)->create([
        'title' => 'V3',
        'content' => $revisionService->generateDiff($v2, $v3),
    ]);

    deleteJson(route('admin.articles.revisions.destroy', [$article, $diff1]))
        ->assertSuccessful();

    expect(ArticleRevision::find($diff1->id))->toBeNull();

    getJson(route('admin.articles.revisions.show', [$article, $base->fresh()]))
        ->assertOk()
        ->assertJson(['content' => $v1]);

    getJson(route('admin.articles.revisions.show', [$article, $diff2->fresh()]))
        ->assertOk()
        ->assertJson(['content' => $v3]);
});

it('requires authentication to delete a revision', function (): void {
    auth()->logout();

    $article = Article::factory()->create();
    $revision = ArticleRevision::factory()->for($article)->create();

    deleteJson(route('admin.articles.revisions.destroy', [$article, $revision]))
        ->assertUnauthorized();
});
