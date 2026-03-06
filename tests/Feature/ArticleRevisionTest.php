<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use App\Services\RevisionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('does not create a revision when storing a new article', function (): void {
    post(route('admin.articles.store'), [
        'title' => 'My New Article',
        'slug' => 'my-new-article',
        'content' => 'Some content here.',
        'status' => Status::Private->value,
    ])->assertRedirect();

    $article = Article::where('slug', 'my-new-article')->first();

    expect($article)->not->toBeNull()
        ->and($article->revisions)->toHaveCount(0);
});

it('creates base and diff revisions on first edit with changed content', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Original Title',
        'content' => 'Original content.',
    ]);

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content.',
        'status' => $article->status->value,
    ])->assertRedirect();

    $article->refresh();
    $revisions = $article->revisions()->oldest('id')->get();

    expect($revisions)->toHaveCount(2)
        ->and($revisions[0]->title)->toBe('Original Title')
        ->and($revisions[0]->content)->toBe('Original content.')
        ->and($revisions[1]->title)->toBe('Updated Title')
        ->and($revisions[1]->content)->toContain('@@');
});

it('stores first revision as full content and second as diff', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Title V1',
        'content' => 'Content version one.',
    ]);

    // First save unchanged — creates no revisions
    put(route('admin.articles.update', $article), [
        'title' => 'Title V1',
        'slug' => $article->slug,
        'content' => 'Content version one.',
        'status' => $article->status->value,
    ])->assertRedirect();

    expect($article->revisions()->count())->toBe(0);

    // Second save with changes — creates base + diff (2 revisions)
    put(route('admin.articles.update', $article), [
        'title' => 'Title V2',
        'slug' => $article->slug,
        'content' => 'Content version two.',
        'status' => $article->status->value,
    ])->assertRedirect();

    $revisions = $article->revisions()->oldest('id')->get();

    expect($revisions)->toHaveCount(2)
        // First revision is full content (pre-edit state)
        ->and($revisions[0]->content)->toBe('Content version one.')
        // Second revision is a diff (contains @@ markers)
        ->and($revisions[1]->content)->toContain('@@');
});

it('reconstructs content from diff chain', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Title V1',
        'content' => 'Content version one.',
    ]);

    // First edit — creates base + diff (2 revisions)
    put(route('admin.articles.update', $article), [
        'title' => 'Title V2',
        'slug' => $article->slug,
        'content' => 'Content version two.',
        'status' => $article->status->value,
    ])->assertRedirect();

    $revisionService = app(RevisionService::class);
    $latestRevision = $article->revisions()->latest('id')->first();
    $reconstructed = $revisionService->reconstructContent($article, $latestRevision);

    expect($reconstructed)->toBe('Content version two.');
});

it('does not create a duplicate revision when content is unchanged', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Same Title',
        'content' => 'Same content.',
    ]);

    $article->revisions()->create([
        'title' => 'Same Title',
        'content' => 'Same content.',
    ]);

    put(route('admin.articles.update', $article), [
        'title' => 'Same Title',
        'slug' => $article->slug,
        'content' => 'Same content.',
        'status' => $article->status->value,
    ])->assertRedirect();

    expect($article->revisions()->count())->toBe(1);
});

it('creates base and diff revisions when title changes but content stays the same', function (): void {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Original Title',
        'content' => 'Same content.',
    ]);

    put(route('admin.articles.update', $article), [
        'title' => 'New Title',
        'slug' => $article->slug,
        'content' => 'Same content.',
        'status' => $article->status->value,
    ])->assertRedirect();

    // First edit with no prior revisions: creates base + diff = 2
    expect($article->revisions()->count())->toBe(2);
});

it('deletes revisions when article is deleted', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    ArticleRevision::factory()->for($article)->create();

    expect(ArticleRevision::count())->toBe(1);

    $article->delete();

    expect(ArticleRevision::count())->toBe(0);
});

it('loads revisions in the customizer edit page', function (): void {
    $article = Article::factory()->create(['user_id' => $this->user->id]);
    ArticleRevision::factory()->for($article)->create();

    get(route('admin.articles.edit', $article))
        ->assertSuccessful()
        ->assertViewIs('admin.articles.customizer');
});
