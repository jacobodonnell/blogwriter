<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use App\Services\RevisionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function (): void {
    foreach (glob(storage_path('framework/testing/bw-import-*.zip')) as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
});

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build an in-memory ZIP as an UploadedFile.
 *
 * @param  array<string, string>  $files  filename => contents
 */
function makeImportZip(array $files): UploadedFile
{
    $tmpPath = storage_path('framework/testing/bw-import-'.uniqid().'.zip');
    $za = new ZipArchive();
    $za->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    // ZipArchive won't write a file if it's empty, so always include a placeholder
    $za->addFromString('.keep', '');
    foreach ($files as $name => $contents) {
        $za->addFromString($name, $contents);
    }
    $za->close();

    return new UploadedFile($tmpPath, 'import.zip', 'application/zip', null, true);
}

/**
 * Build a Markdown article file string.
 *
 * @param  array<string, mixed>  $frontmatter
 */
function makeArticleMd(array $frontmatter, string $content = 'Hello world.'): string
{
    return "---\n".Yaml::dump($frontmatter, 2, 2)."---\n\n".$content;
}

/**
 * Build a revision Markdown file string.
 */
function makeRevisionMd(string $title, string $createdAt, string $content): string
{
    $frontmatter = ['title' => $title, 'created_at' => $createdAt];

    return "---\n".Yaml::dump($frontmatter, 2, 2)."---\n\n".$content;
}

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

it('rejects non-zip files with 422', function (): void {
    $file = UploadedFile::fake()->create('import.txt', 10, 'text/plain');

    $this->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.import.articles'), [
            'file' => $file,
            'duplicate_strategy' => 'skip',
        ])->assertStatus(422);
});

it('rejects missing duplicate_strategy with 422', function (): void {
    $zip = makeImportZip([]);

    $this->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.import.articles'), [
            'file' => $zip,
        ])->assertStatus(422);
});

it('rejects invalid duplicate_strategy with 422', function (): void {
    $zip = makeImportZip([]);

    $this->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.import.articles'), [
            'file' => $zip,
            'duplicate_strategy' => 'delete',
        ])->assertStatus(422);
});

// ---------------------------------------------------------------------------
// Basic import
// ---------------------------------------------------------------------------

it('imports articles from a valid zip', function (): void {
    $md1 = makeArticleMd(['title' => 'First Article', 'slug' => 'first-article', 'status' => 'public', 'published_at' => '2024-01-01T00:00:00+00:00']);
    $md2 = makeArticleMd(['title' => 'Second Article', 'slug' => 'second-article', 'status' => 'public', 'published_at' => '2024-01-02T00:00:00+00:00']);

    $zip = makeImportZip([
        'articles/first-article.md' => $md1,
        'articles/second-article.md' => $md2,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 2, 'skipped' => 0]);

    expect(Article::query()->count())->toBe(2);
});

it('returns ok with zero imported for an empty zip', function (): void {
    $zip = makeImportZip([]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 0, 'skipped' => 0]);
});

// ---------------------------------------------------------------------------
// Duplicate strategies
// ---------------------------------------------------------------------------

it('skips duplicate slugs when strategy is skip', function (): void {
    Article::factory()->create(['slug' => 'existing-article', 'title' => 'Original Title']);

    $md = makeArticleMd(['title' => 'New Title', 'slug' => 'existing-article', 'status' => 'public', 'published_at' => '2024-01-01T00:00:00+00:00']);
    $fresh = makeArticleMd(['title' => 'Fresh Article', 'slug' => 'fresh-article', 'status' => 'public', 'published_at' => '2024-01-01T00:00:00+00:00']);

    $zip = makeImportZip([
        'articles/existing-article.md' => $md,
        'articles/fresh-article.md' => $fresh,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1, 'skipped' => 1]);

    expect(Article::query()->where('slug', 'existing-article')->value('title'))->toBe('Original Title');
});

it('overwrites duplicate slugs when strategy is overwrite', function (): void {
    Article::factory()->create(['slug' => 'my-article', 'title' => 'Old Title']);

    $md = makeArticleMd(['title' => 'Updated Title', 'slug' => 'my-article', 'status' => 'public', 'published_at' => '2024-01-01T00:00:00+00:00']);

    $zip = makeImportZip(['articles/my-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'overwrite',
    ])->assertJson(['status' => 'ok', 'imported' => 1, 'skipped' => 0]);

    expect(Article::query()->where('slug', 'my-article')->value('title'))->toBe('Updated Title');
});

// ---------------------------------------------------------------------------
// Category resolution
// ---------------------------------------------------------------------------

it('resolves category slug to category_id on import', function (): void {
    $category = Category::factory()->create(['slug' => 'tech']);

    $md = makeArticleMd([
        'title' => 'Tech Article',
        'slug' => 'tech-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'category' => 'tech',
    ]);

    $zip = makeImportZip(['articles/tech-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $article = Article::query()->where('slug', 'tech-article')->first();
    expect($article->category_id)->toBe($category->id);
});

it('returns preflight_warning when article references unknown category slug', function (): void {
    $md = makeArticleMd([
        'title' => 'Mystery Article',
        'slug' => 'mystery-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'category' => 'unknown-cat',
    ]);

    $zip = makeImportZip(['articles/mystery-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson([
        'status' => 'preflight_warning',
        'missing' => ['unknown-cat'],
    ]);

    expect(Article::query()->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Confirm endpoint
// ---------------------------------------------------------------------------

it('confirm imports articles with category_id null for missing categories', function (): void {
    $md = makeArticleMd([
        'title' => 'No Category',
        'slug' => 'no-category',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'category' => 'unknown-cat',
    ]);

    $zip = makeImportZip(['articles/no-category.md' => $md]);

    $warningResponse = $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'preflight_warning']);

    $token = $warningResponse->json('token');

    $this->post(route('admin.import.articles.confirm'), [
        'token' => $token,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'no-category')->first();
    expect($article->category_id)->toBeNull();
});

it('returns error for stale or missing session token on confirm', function (): void {
    $this->post(route('admin.import.articles.confirm'), [
        'token' => 'nonexistent-token',
        'duplicate_strategy' => 'skip',
    ])->assertStatus(422)
        ->assertJson(['status' => 'error']);
});

// ---------------------------------------------------------------------------
// categories.yaml import
// ---------------------------------------------------------------------------

it('creates categories from categories.yaml and imports articles without warning', function (): void {
    $categoriesYaml = Yaml::dump([
        ['id' => 1, 'name' => 'Technology', 'slug' => 'technology', 'description' => null, 'parent_slug' => null],
    ], 2, 2);

    $md = makeArticleMd([
        'title' => 'Tech Post',
        'slug' => 'tech-post',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'category' => 'technology',
    ]);

    $zip = makeImportZip([
        'categories.yaml' => $categoriesYaml,
        'articles/tech-post.md' => $md,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    expect(Category::query()->where('slug', 'technology')->exists())->toBeTrue();
});

it('fires preflight warning when categories.yaml present but article has extra unknown category', function (): void {
    $categoriesYaml = Yaml::dump([
        ['id' => 1, 'name' => 'Technology', 'slug' => 'technology', 'description' => null, 'parent_slug' => null],
    ], 2, 2);

    $md = makeArticleMd([
        'title' => 'Mystery Post',
        'slug' => 'mystery-post',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'category' => 'extra-unknown',
    ]);

    $zip = makeImportZip([
        'categories.yaml' => $categoriesYaml,
        'articles/mystery-post.md' => $md,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'preflight_warning', 'missing' => ['extra-unknown']]);
});

// ---------------------------------------------------------------------------
// Field mapping
// ---------------------------------------------------------------------------

it('maps featured_image_url in frontmatter to external_featured_img_url column', function (): void {
    $md = makeArticleMd([
        'title' => 'Image Article',
        'slug' => 'image-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'featured_image_url' => 'https://cdn.example.com/hero.jpg',
    ]);

    $zip = makeImportZip(['articles/image-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $article = Article::query()->where('slug', 'image-article')->first();
    expect($article->external_featured_img_url)->toBe('https://cdn.example.com/hero.jpg');
});

// ---------------------------------------------------------------------------
// Error cases
// ---------------------------------------------------------------------------

it('rejects a corrupted (non-zip) binary file with 422', function (): void {
    $tmpPath = storage_path('framework/testing/bw-import-'.uniqid().'.zip');
    file_put_contents($tmpPath, random_bytes(256));
    $file = new UploadedFile($tmpPath, 'import.zip', 'application/zip', null, true);

    $this->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.import.articles'), [
            'file' => $file,
            'duplicate_strategy' => 'skip',
        ])->assertStatus(422);
});

it('gracefully skips articles with invalid yaml frontmatter', function (): void {
    $badMd = "---\ntitle: [unclosed\n---\n\nHello world.";

    $zip = makeImportZip(['articles/bad-frontmatter.md' => $badMd]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['imported' => 0]);
});

it('returns ok with zero imported and zero skipped for a zip with no md files', function (): void {
    $zip = makeImportZip([]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 0, 'skipped' => 0]);
});

// ---------------------------------------------------------------------------
// settings.yaml import
// ---------------------------------------------------------------------------

it('applies settings from settings.yaml on import', function (): void {
    $settingsYaml = Yaml::dump([
        'profile_name' => 'Imported User',
        'theme_light' => 'lofi',
        'theme_dark' => 'dracula',
        'theme_font' => 'inter',
        'profile_bio' => 'An imported bio.',
        'page_home_subtitle' => 'Hello from import!',
    ], 2, 2);

    $zip = makeImportZip(['settings.yaml' => $settingsYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    expect(User::query()->value('name'))->toBe('Imported User')
        ->and(App\Models\Setting::get('theme_light'))->toBe('lofi')
        ->and(App\Models\Setting::get('theme_dark'))->toBe('dracula')
        ->and(App\Models\Setting::get('theme_font'))->toBe('inter')
        ->and(App\Models\Setting::get('profile_bio'))->toBe('An imported bio.')
        ->and(App\Models\Setting::get('page_home_subtitle'))->toBe('Hello from import!');
});

it('ignores unknown theme_light value on import', function (): void {
    App\Models\Setting::set('theme_light', 'lofi');

    $settingsYaml = Yaml::dump(['theme_light' => 'totally-fake-theme'], 2, 2);

    $zip = makeImportZip(['settings.yaml' => $settingsYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    expect(App\Models\Setting::get('theme_light'))->toBe('lofi');
});

it('ignores unknown theme_font value on import', function (): void {
    App\Models\Setting::set('theme_font', 'noto-sans');

    $settingsYaml = Yaml::dump(['theme_font' => 'comic-sans-ms'], 2, 2);

    $zip = makeImportZip(['settings.yaml' => $settingsYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    expect(App\Models\Setting::get('theme_font'))->toBe('noto-sans');
});

it('silently succeeds when settings.yaml is absent from zip', function (): void {
    $zip = makeImportZip([]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);
});

// ---------------------------------------------------------------------------
// H1→H2 normalisation
// ---------------------------------------------------------------------------

it('silently converts H1 headings to H2 during import', function (): void {
    $content = "# Top Heading\n\nSome body text.\n\n## Already H2\n\n# Another H1";

    $md = makeArticleMd([
        'title' => 'H1 Article',
        'slug' => 'h1-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], $content);

    $zip = makeImportZip(['articles/h1-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'h1-article')->first();

    expect($article->content)
        ->toContain('## Top Heading')
        ->toContain('## Already H2')
        ->toContain('## Another H1');

    // Ensure no bare H1s remain (a single # not followed by #)
    expect($article->content)->not->toMatch('/^# (?!#)/m');
});

// ---------------------------------------------------------------------------
// photos.yaml import
// ---------------------------------------------------------------------------

it('creates photos from photos.yaml on import', function (): void {
    $photosYaml = Yaml::dump([
        [
            'slug' => 'imported-photo',
            'filename' => 'imported.jpg',
            'caption' => 'An imported caption.',
            'alt_text' => 'Alt text here',
            'status' => 'public',
            'published_at' => '2024-06-01T12:00:00+00:00',
        ],
    ], 3, 2);

    $zip = makeImportZip(['photos.yaml' => $photosYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $photo = Photo::query()->where('slug', 'imported-photo')->first();
    expect($photo)->not->toBeNull()
        ->and($photo->caption)->toBe('An imported caption.')
        ->and($photo->alt_text)->toBe('Alt text here')
        ->and($photo->status->value)->toBe('public');
});

it('skips existing photos when strategy is skip', function (): void {
    Photo::factory()->create(['slug' => 'existing-photo', 'caption' => 'Original caption']);

    $photosYaml = Yaml::dump([
        ['slug' => 'existing-photo', 'filename' => 'photo.jpg', 'alt_text' => 'Alt', 'status' => 'public'],
    ], 3, 2);

    $zip = makeImportZip(['photos.yaml' => $photosYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    expect(Photo::query()->where('slug', 'existing-photo')->value('caption'))->toBe('Original caption');
});

it('overwrites existing photos when strategy is overwrite', function (): void {
    Photo::factory()->create(['slug' => 'existing-photo', 'caption' => 'Old caption']);

    $photosYaml = Yaml::dump([
        ['slug' => 'existing-photo', 'filename' => 'photo.jpg', 'alt_text' => 'Alt', 'status' => 'public', 'caption' => 'New caption'],
    ], 3, 2);

    $zip = makeImportZip(['photos.yaml' => $photosYaml]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'overwrite',
    ])->assertJson(['status' => 'ok']);

    expect(Photo::query()->where('slug', 'existing-photo')->value('caption'))->toBe('New caption');
});

it('silently succeeds when photos.yaml is absent from zip', function (): void {
    $zip = makeImportZip([]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    expect(Photo::query()->count())->toBe(0);
});

it('attaches image file to photo from images/ directory in zip', function (): void {
    Storage::fake('public');

    $fakeImageContent = UploadedFile::fake()->image('photo.jpg', 50, 50)->get();
    $fakeUuid = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
    $imageFilename = $fakeUuid.'-photo.jpg';

    $photosYaml = Yaml::dump([
        [
            'slug' => 'photo-with-image',
            'filename' => 'photo.jpg',
            'alt_text' => 'Test image',
            'status' => 'public',
            'image_file' => $imageFilename,
        ],
    ], 3, 2);

    $zip = makeImportZip([
        'photos.yaml' => $photosYaml,
        'images/'.$imageFilename => $fakeImageContent,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $photo = Photo::query()->where('slug', 'photo-with-image')->first();
    expect($photo)->not->toBeNull()
        ->and($photo->getFirstMedia('image'))->not->toBeNull();
});

it('reconnects photo_id on articles via photo_slug frontmatter', function (): void {
    $photo = Photo::factory()->create(['slug' => 'my-hero-photo']);

    $photosYaml = Yaml::dump([
        ['slug' => 'my-hero-photo', 'filename' => 'hero.jpg', 'alt_text' => 'Hero', 'status' => 'public'],
    ], 3, 2);

    $md = makeArticleMd([
        'title' => 'Photo Article',
        'slug' => 'photo-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
        'photo_slug' => 'my-hero-photo',
    ]);

    $zip = makeImportZip([
        'photos.yaml' => $photosYaml,
        'articles/photo-article.md' => $md,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $article = Article::query()->where('slug', 'photo-article')->first();
    expect($article->photo_id)->toBe($photo->id);
});

it('imports articles with CRLF line endings', function (): void {
    $frontmatter = Yaml::dump([
        'title' => 'CRLF Article',
        'slug' => 'crlf-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 2, 2);

    // Build the file with CRLF line endings
    $md = "---\n".$frontmatter."---\n\nHello from Windows.";
    $md = str_replace("\n", "\r\n", $md);

    $zip = makeImportZip(['articles/crlf-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'crlf-article')->first();
    expect($article)->not->toBeNull()
        ->and($article->title)->toBe('CRLF Article');
});

it('generates slug from title when slug is missing', function (): void {
    $md = makeArticleMd([
        'title' => 'My Great Article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ]);

    $zip = makeImportZip(['articles/my-great-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'my-great-article')->first();
    expect($article)->not->toBeNull()
        ->and($article->title)->toBe('My Great Article');
});

it('defaults to private status when status key is absent', function (): void {
    $md = makeArticleMd([
        'title' => 'No Status Article',
        'slug' => 'no-status-article',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ]);

    $zip = makeImportZip(['articles/no-status-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'no-status-article')->first();
    expect($article->status)->toBe(App\Enums\Status::Private)
        ->and($article->published_at?->toIso8601String())->toBe('2024-01-01T00:00:00+00:00');
});

it('sets published_at to now when public article has no published_at', function (): void {
    $md = makeArticleMd([
        'title' => 'No Date Public',
        'slug' => 'no-date-public',
        'status' => 'public',
    ]);

    $zip = makeImportZip(['articles/no-date-public.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'no-date-public')->first();
    expect($article->status)->toBe(App\Enums\Status::Public)
        ->and($article->published_at)->not->toBeNull();
});

it('preserves published_at on private articles', function (): void {
    $md = makeArticleMd([
        'title' => 'Private With Date',
        'slug' => 'private-with-date',
        'status' => 'private',
        'published_at' => '2024-06-15T10:00:00+00:00',
    ]);

    $zip = makeImportZip(['articles/private-with-date.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'private-with-date')->first();
    expect($article->status)->toBe(App\Enums\Status::Private)
        ->and($article->published_at->toIso8601String())->toBe('2024-06-15T10:00:00+00:00');
});

it('private article with no published_at imports as null', function (): void {
    $md = makeArticleMd([
        'title' => 'Never Published',
        'slug' => 'never-published',
        'status' => 'private',
    ]);

    $zip = makeImportZip(['articles/never-published.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'never-published')->first();
    expect($article->status)->toBe(App\Enums\Status::Private)
        ->and($article->published_at)->toBeNull();
});

it('restores created_at from frontmatter', function (): void {
    $md = makeArticleMd([
        'title' => 'Old Post',
        'slug' => 'old-post',
        'status' => 'public',
        'published_at' => '2023-06-15T10:00:00+00:00',
        'created_at' => '2023-01-01T08:00:00+00:00',
    ]);

    $zip = makeImportZip(['articles/old-post.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok']);

    $article = Article::query()->where('slug', 'old-post')->first();
    expect($article->created_at->toIso8601String())->toBe('2023-01-01T08:00:00+00:00');
});

// ---------------------------------------------------------------------------
// Revisions import — base revision (no revision files)
// ---------------------------------------------------------------------------

it('creates no revisions for imported articles when no revision files present', function (): void {
    $md = makeArticleMd([
        'title' => 'Rev Article',
        'slug' => 'rev-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Some body content.');

    $zip = makeImportZip(['articles/rev-article.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'rev-article')->first();
    expect($article->revisions()->count())->toBe(0);
});

it('overwrites delete existing revisions and creates no base revision without revision files', function (): void {
    $article = Article::factory()->create(['slug' => 'overwrite-rev', 'title' => 'Old Title', 'content' => 'Old content']);
    ArticleRevision::factory()->count(3)->create(['article_id' => $article->id]);

    expect($article->revisions()->count())->toBe(3);

    $md = makeArticleMd([
        'title' => 'New Title',
        'slug' => 'overwrite-rev',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'New content.');

    $zip = makeImportZip(['articles/overwrite-rev.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'overwrite',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article->refresh();
    expect($article->revisions()->count())->toBe(0);
});

it('skipped articles get no revision changes', function (): void {
    $article = Article::factory()->create(['slug' => 'skip-rev', 'title' => 'Existing']);
    ArticleRevision::factory()->count(2)->create(['article_id' => $article->id]);

    $md = makeArticleMd([
        'title' => 'Skip Rev',
        'slug' => 'skip-rev',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ]);

    $zip = makeImportZip(['articles/skip-rev.md' => $md]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'skipped' => 1]);

    expect($article->revisions()->count())->toBe(2);
});

// ---------------------------------------------------------------------------
// Directory-based revisions import
// ---------------------------------------------------------------------------

it('imports article with directory-based revisions', function (): void {
    $currentMd = makeArticleMd([
        'title' => 'Dir Rev Article',
        'slug' => 'dir-rev',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Current content.');

    $rev1 = makeRevisionMd('First Draft', '2024-01-01T00:00:00+00:00', 'Initial content.');
    $rev2 = makeRevisionMd('Second Draft', '2024-01-02T00:00:00+00:00', 'Updated content.');

    $zip = makeImportZip([
        'articles/dir-rev/current.md' => $currentMd,
        'articles/dir-rev/revisions/2024-01-01T00-00-00Z.md' => $rev1,
        'articles/dir-rev/revisions/2024-01-02T00-00-00Z.md' => $rev2,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'dir-rev')->first();
    expect($article)->not->toBeNull()
        ->and($article->content)->toBe('Current content.');

    $revisions = $article->revisions()->oldest('id')->get();
    expect($revisions)->toHaveCount(2)
        ->and($revisions[0]->title)->toBe('First Draft')
        ->and($revisions[0]->created_at->toIso8601String())->toBe('2024-01-01T00:00:00+00:00')
        ->and($revisions[1]->title)->toBe('Second Draft')
        ->and($revisions[1]->created_at->toIso8601String())->toBe('2024-01-02T00:00:00+00:00');
});

it('directory-based revision content is correctly reconstructable after import', function (): void {
    $revisionService = app(RevisionService::class);

    $currentMd = makeArticleMd([
        'title' => 'Reconstruct Test',
        'slug' => 'reconstruct-test',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Final version.');

    $rev1 = makeRevisionMd('V1', '2024-01-01T00:00:00+00:00', 'Version one content.');
    $rev2 = makeRevisionMd('V2', '2024-01-02T00:00:00+00:00', 'Version two content.');
    $rev3 = makeRevisionMd('V3', '2024-01-03T00:00:00+00:00', 'Version three content.');

    $zip = makeImportZip([
        'articles/reconstruct-test/current.md' => $currentMd,
        'articles/reconstruct-test/revisions/2024-01-01T00-00-00Z.md' => $rev1,
        'articles/reconstruct-test/revisions/2024-01-02T00-00-00Z.md' => $rev2,
        'articles/reconstruct-test/revisions/2024-01-03T00-00-00Z.md' => $rev3,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'reconstruct-test')->first();
    $revisions = $article->revisions()->oldest('id')->get();

    // First revision should be full content
    expect($revisions[0]->content)->toBe('Version one content.');

    // Subsequent revisions should be diffs, but reconstructContent should give back original content
    expect($revisionService->reconstructContent($article, $revisions[0]))->toBe('Version one content.')
        ->and($revisionService->reconstructContent($article, $revisions[1]))->toBe('Version two content.')
        ->and($revisionService->reconstructContent($article, $revisions[2]))->toBe('Version three content.');
});

it('mixed flat and directory articles import correctly', function (): void {
    $flatMd = makeArticleMd([
        'title' => 'Flat Article',
        'slug' => 'flat-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Flat content.');

    $dirCurrentMd = makeArticleMd([
        'title' => 'Dir Article',
        'slug' => 'dir-article',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Dir content.');

    $rev1 = makeRevisionMd('Rev', '2024-01-01T00:00:00+00:00', 'Rev content.');

    $zip = makeImportZip([
        'articles/flat-article.md' => $flatMd,
        'articles/dir-article/current.md' => $dirCurrentMd,
        'articles/dir-article/revisions/2024-01-01T00-00-00Z.md' => $rev1,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 2]);

    $flat = Article::query()->where('slug', 'flat-article')->first();
    $dir = Article::query()->where('slug', 'dir-article')->first();

    expect($flat)->not->toBeNull()
        ->and($flat->content)->toBe('Flat content.')
        ->and($dir)->not->toBeNull()
        ->and($dir->content)->toBe('Dir content.');

    // Flat article gets no revisions, dir article gets imported revision
    expect($flat->revisions()->count())->toBe(0)
        ->and($dir->revisions()->count())->toBe(1)
        ->and($dir->revisions()->first()->title)->toBe('Rev');
});

it('articles missing from revision files get no revisions', function (): void {
    $md1 = makeArticleMd([
        'title' => 'Has Dir Revs',
        'slug' => 'has-dir-revs',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Content A');

    $md2 = makeArticleMd([
        'title' => 'No Dir Revs',
        'slug' => 'no-dir-revs',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Content B');

    $rev1 = makeRevisionMd('Dir Rev', '2024-01-01T00:00:00+00:00', 'Dir content');

    $zip = makeImportZip([
        'articles/has-dir-revs/current.md' => $md1,
        'articles/has-dir-revs/revisions/2024-01-01T00-00-00Z.md' => $rev1,
        'articles/no-dir-revs.md' => $md2,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 2]);

    $articleWithDir = Article::query()->where('slug', 'has-dir-revs')->first();
    $articleWithoutDir = Article::query()->where('slug', 'no-dir-revs')->first();

    // Article with dir revisions gets those revisions
    expect($articleWithDir->revisions()->count())->toBe(1)
        ->and($articleWithDir->revisions()->first()->title)->toBe('Dir Rev');

    // Article without dir revisions gets no revisions
    expect($articleWithoutDir->revisions()->count())->toBe(0);
});

// ---------------------------------------------------------------------------
// Roundtrip: export → import preserves revision content
// ---------------------------------------------------------------------------

it('roundtrip export then import preserves revision content', function (): void {
    $revisionService = app(RevisionService::class);

    // Create an article with a real diff chain
    $article = Article::factory()->published()->create([
        'slug' => 'roundtrip',
        'title' => 'Roundtrip Article',
        'content' => 'Final content.',
    ]);

    // Base revision (full content)
    ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Rev One',
        'content' => 'Initial content.',
        'created_at' => now()->subMinutes(10),
    ]);

    // Second revision (diff)
    $diff = $revisionService->generateDiff('Initial content.', 'Updated content.');
    ArticleRevision::factory()->create([
        'article_id' => $article->id,
        'title' => 'Rev Two',
        'content' => $diff,
        'created_at' => now()->subMinutes(5),
    ]);

    // Export to ZIP
    $response = $this->get(route('admin.articles.download', $article));
    $zipBytes = $response->streamedContent();

    // Verify export has directory structure
    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);
    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    expect($za->getFromName('articles/roundtrip/current.md'))->not->toBeFalse();
    $za->close();
    unlink($tmpFile);

    // Delete the original article and its revisions
    $article->revisions()->delete();
    $article->delete();
    expect(Article::query()->where('slug', 'roundtrip')->exists())->toBeFalse();

    // Import the exported ZIP
    $tmpPath = storage_path('framework/testing/bw-import-'.uniqid().'.zip');
    file_put_contents($tmpPath, $zipBytes);
    $zip = new UploadedFile($tmpPath, 'import.zip', 'application/zip', null, true);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    // Verify article and revisions survived the roundtrip
    $imported = Article::query()->where('slug', 'roundtrip')->first();
    expect($imported)->not->toBeNull()
        ->and($imported->title)->toBe('Roundtrip Article');

    $revisions = $imported->revisions()->oldest('id')->get();
    expect($revisions)->toHaveCount(2)
        ->and($revisions[0]->title)->toBe('Rev One')
        ->and($revisions[1]->title)->toBe('Rev Two');

    // Verify reconstruction gives back the original full content
    expect($revisionService->reconstructContent($imported, $revisions[0]))->toBe('Initial content.')
        ->and($revisionService->reconstructContent($imported, $revisions[1]))->toBe('Updated content.');
});

it('skips revisions with empty content during import', function (): void {
    $currentMd = makeArticleMd([
        'title' => 'Empty Rev Test',
        'slug' => 'empty-rev-test',
        'status' => 'public',
        'published_at' => '2024-01-01T00:00:00+00:00',
    ], 'Current content.');

    $emptyRev = makeRevisionMd('Empty Rev', '2024-01-01T00:00:00+00:00', '');

    $zip = makeImportZip([
        'articles/empty-rev-test/current.md' => $currentMd,
        'articles/empty-rev-test/revisions/2024-01-01T00-00-00Z.md' => $emptyRev,
    ]);

    $this->post(route('admin.import.articles'), [
        'file' => $zip,
        'duplicate_strategy' => 'skip',
    ])->assertJson(['status' => 'ok', 'imported' => 1]);

    $article = Article::query()->where('slug', 'empty-rev-test')->first();
    expect($article)->not->toBeNull()
        ->and($article->revisions)->toHaveCount(0);
});
