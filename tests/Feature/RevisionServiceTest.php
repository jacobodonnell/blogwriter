<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Services\RevisionService;

beforeEach(function (): void {
    $this->service = new RevisionService;
});

it('reconstructs base revision without applying sibling diffs created at the same timestamp', function (): void {
    $article = Article::factory()->create(['content' => 'Third version']);

    $now = now();

    // Base revision and first diff created at the same timestamp (same request)
    $base = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => 'First version',
        'created_at' => $now,
    ]);

    ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff('First version', 'Second version'),
        'created_at' => $now,
    ]);

    $reconstructed = $this->service->reconstructContent($article, $base);

    expect($reconstructed)->toBe('First version');
});

it('reconstructs diff revisions by applying diffs to the base', function (): void {
    $article = Article::factory()->create(['content' => 'Third version']);

    $now = now();

    ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => 'First version',
        'created_at' => $now,
    ]);

    $diff1 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff('First version', 'Second version'),
        'created_at' => $now,
    ]);

    $diff2 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff('Second version', 'Third version'),
        'created_at' => $now->copy()->addMinute(),
    ]);

    expect($this->service->reconstructContent($article, $diff1))->toBe('Second version')
        ->and($this->service->reconstructContent($article, $diff2))->toBe('Third version');
});

it('reconstructs multi-line content through a chain of diffs', function (): void {
    $article = Article::factory()->create();

    $v1 = "This is the first version of the article.\nInitial draft content.";
    $v2 = "This is the SECOND revision.\nContent has been updated.";
    $v3 = "This is the THIRD revision.\nMore changes here.";

    $now = now();

    $base = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $v1,
        'created_at' => $now,
    ]);

    $diff1 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v1, $v2),
        'created_at' => $now,
    ]);

    $diff2 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v2, $v3),
        'created_at' => $now->copy()->addMinute(),
    ]);

    expect($this->service->reconstructContent($article, $base))->toBe($v1)
        ->and($this->service->reconstructContent($article, $diff1))->toBe($v2)
        ->and($this->service->reconstructContent($article, $diff2))->toBe($v3);
});

it('deletes a non-base revision and preserves reconstruction of remaining revisions', function (): void {
    $article = Article::factory()->create();

    $v1 = 'First version';
    $v2 = 'Second version';
    $v3 = 'Third version';

    $base = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $v1,
    ]);

    $diff1 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v1, $v2),
    ]);

    $diff2 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v2, $v3),
    ]);

    $this->service->deleteRevision($article, $diff1);

    expect(ArticleRevision::find($diff1->id))->toBeNull()
        ->and($article->revisions()->count())->toBe(2)
        ->and($this->service->reconstructContent($article, $base->fresh()))->toBe($v1)
        ->and($this->service->reconstructContent($article, $diff2->fresh()))->toBe($v3);
});

it('deletes the base revision and promotes the next revision', function (): void {
    $article = Article::factory()->create();

    $v1 = 'First version';
    $v2 = 'Second version';
    $v3 = 'Third version';

    $base = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $v1,
    ]);

    $diff1 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v1, $v2),
    ]);

    $diff2 = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $this->service->generateDiff($v2, $v3),
    ]);

    $this->service->deleteRevision($article, $base);

    expect(ArticleRevision::find($base->id))->toBeNull()
        ->and($article->revisions()->count())->toBe(2);

    $newBase = $article->revisions()->oldest('id')->first();
    expect($newBase->id)->toBe($diff1->id)
        ->and($this->service->reconstructContent($article, $newBase))->toBe($v2)
        ->and($this->service->reconstructContent($article, $diff2->fresh()))->toBe($v3);
});

it('deletes a middle revision in a 5-revision chain and preserves reconstruction', function (): void {
    $article = Article::factory()->create();

    $versions = [
        'Version one',
        'Version two',
        'Version three',
        'Version four',
        'Version five',
    ];

    $base = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => $versions[0],
    ]);

    $revisions = [$base];
    for ($i = 1; $i < 5; $i++) {
        $revisions[] = ArticleRevision::factory()->create([
            'article_id' => $article->id,
            'title' => 'Test',
            'content' => $this->service->generateDiff($versions[$i - 1], $versions[$i]),
        ]);
    }

    // Delete the middle revision (index 2 = "Version three")
    $this->service->deleteRevision($article, $revisions[2]);

    expect(ArticleRevision::find($revisions[2]->id))->toBeNull()
        ->and($article->revisions()->count())->toBe(4);

    // All remaining revisions should still reconstruct correctly
    expect($this->service->reconstructContent($article, $revisions[0]->fresh()))->toBe($versions[0])
        ->and($this->service->reconstructContent($article, $revisions[1]->fresh()))->toBe($versions[1])
        ->and($this->service->reconstructContent($article, $revisions[3]->fresh()))->toBe($versions[3])
        ->and($this->service->reconstructContent($article, $revisions[4]->fresh()))->toBe($versions[4]);
});

it('deletes the only revision cleanly', function (): void {
    $article = Article::factory()->create();

    $revision = ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Test',
        'content' => 'Only version',
    ]);

    $this->service->deleteRevision($article, $revision);

    expect(ArticleRevision::find($revision->id))->toBeNull()
        ->and($article->revisions()->count())->toBe(0);
});
