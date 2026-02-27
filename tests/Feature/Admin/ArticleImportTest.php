<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
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
    $md1 = makeArticleMd(['title' => 'First Article', 'slug' => 'first-article', 'draft' => false, 'date' => '2024-01-01T00:00:00+00:00']);
    $md2 = makeArticleMd(['title' => 'Second Article', 'slug' => 'second-article', 'draft' => false, 'date' => '2024-01-02T00:00:00+00:00']);

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

    $md = makeArticleMd(['title' => 'New Title', 'slug' => 'existing-article', 'draft' => false, 'date' => '2024-01-01T00:00:00+00:00']);
    $fresh = makeArticleMd(['title' => 'Fresh Article', 'slug' => 'fresh-article', 'draft' => false, 'date' => '2024-01-01T00:00:00+00:00']);

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

    $md = makeArticleMd(['title' => 'Updated Title', 'slug' => 'my-article', 'draft' => false, 'date' => '2024-01-01T00:00:00+00:00']);

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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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
        'draft' => false,
        'date' => '2024-01-01T00:00:00+00:00',
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

it('restores created_at from frontmatter', function (): void {
    $md = makeArticleMd([
        'title' => 'Old Post',
        'slug' => 'old-post',
        'draft' => false,
        'date' => '2023-06-15T10:00:00+00:00',
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
