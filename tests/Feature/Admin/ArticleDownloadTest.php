<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('downloads an article as a zip file', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
        'title' => 'My Article',
        'content' => 'Hello world.',
    ]);

    $response = $this->get(route('admin.articles.download', $article));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/zip');
    expect($response->headers->get('Content-Disposition'))->toContain('my-article-export-');
});

it('zip download contains article markdown with frontmatter and body', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
        'title' => 'My Article',
        'content' => 'Hello world.',
    ]);

    $response = $this->get(route('admin.articles.download', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('articles/my-article.md');
    $za->close();
    unlink($tmpFile);

    expect($contents)
        ->toContain('title:')
        ->toContain('My Article')
        ->toContain('slug: my-article')
        ->toContain('Hello world.');
});

it('redirects unauthenticated users from the article download endpoint', function (): void {
    auth()->logout();

    $article = Article::factory()->published()->create();

    $this->get(route('admin.articles.download', $article))
        ->assertRedirect(route('login'));
});

it('returns 404 for a non-existent article download', function (): void {
    $this->get(route('admin.articles.download', 99999))
        ->assertNotFound();
});
