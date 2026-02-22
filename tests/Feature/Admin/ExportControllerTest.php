<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\ArticleExportService;
use Illuminate\Database\Eloquent\Collection;
use ZipStream\ZipStream;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// --- Page ---

it('loads the export page for authenticated users', function (): void {
    $this->get(route('admin.settings.export'))
        ->assertSuccessful()
        ->assertSee('Export');
});

it('redirects unauthenticated users from the export page', function (): void {
    auth()->logout();

    $this->get(route('admin.settings.export'))
        ->assertRedirect(route('login'));
});

it('shows the article count on the export page', function (): void {
    Article::factory()->count(3)->create();

    $this->get(route('admin.settings.export'))
        ->assertSuccessful()
        ->assertSee('3');
});

it('shows the export tab in the settings navigation', function (): void {
    $this->get(route('admin.settings.profile'))
        ->assertSuccessful()
        ->assertSee('Export');
});

// --- Download response ---

it('returns a zip download response', function (): void {
    $response = $this->post(route('admin.export.articles'));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/zip');
    expect($response->headers->get('Content-Disposition'))->toContain('blogwriter-export-');
});

it('redirects unauthenticated users from the export POST endpoint', function (): void {
    auth()->logout();

    $this->post(route('admin.export.articles'))
        ->assertRedirect(route('login'));
});

// --- Service unit tests ---

it('builds correct frontmatter for a published article', function (): void {
    $category = Category::factory()->create(['slug' => 'tech']);
    $article = Article::factory()->published()->create([
        'slug' => 'my-article',
        'summary' => 'A brief summary.',
        'category_id' => $category->id,
        'past_slugs' => ['old-slug'],
        'meta' => [
            'meta_title' => 'Custom Title',
            'meta_description' => 'Custom desc.',
        ],
    ]);
    $article->load('user', 'category');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['slug'])->toBe('my-article')
        ->and($frontmatter['draft'])->toBeFalse()
        ->and($frontmatter['description'])->toBe('A brief summary.')
        ->and($frontmatter['category'])->toBe('tech')
        ->and($frontmatter['past_slugs'])->toContain('old-slug')
        ->and($frontmatter['meta_title'])->toBe('Custom Title')
        ->and($frontmatter['meta_description'])->toBe('Custom desc.');
});

it('marks draft articles as draft in frontmatter', function (): void {
    $article = Article::factory()->draft()->create(['slug' => 'draft-article']);
    $article->load('user', 'category');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['draft'])->toBeTrue();
});

it('omits null frontmatter fields', function (): void {
    $article = Article::factory()->published()->create([
        'summary' => null,
        'category_id' => null,
        'meta' => null,
    ]);
    $article->load('user', 'category');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('description')
        ->and($frontmatter)->not->toHaveKey('category')
        ->and($frontmatter)->not->toHaveKey('meta_title')
        ->and($frontmatter)->not->toHaveKey('meta_description');
});

it('streams articles as markdown files to a zip', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'content' => 'Some content.',
    ]);
    $article->load('user', 'category');

    $captured = '';
    $stream = fopen('php://memory', 'r+');

    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new ArticleExportService();
    $service->streamToZip($zip, new Collection([$article]));
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    // Write bytes to a temp file and inspect with ZipArchive
    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('articles/hello-world.md');
    $za->close();
    unlink($tmpFile);

    expect($contents)
        ->toContain('title: \'Hello World\'')
        ->toContain('slug: hello-world')
        ->toContain('Some content.');
});
