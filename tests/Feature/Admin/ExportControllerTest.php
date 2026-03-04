<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\Category;
use App\Models\Photo;
use App\Models\User;
use App\Services\ArticleExportService;
use App\Services\PhotoExportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;
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
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['slug'])->toBe('my-article')
        ->and($frontmatter['status'])->toBe('public')
        ->and($frontmatter['description'])->toBe('A brief summary.')
        ->and($frontmatter['category'])->toBe('tech')
        ->and($frontmatter['past_slugs'])->toContain('old-slug')
        ->and($frontmatter['meta_title'])->toBe('Custom Title')
        ->and($frontmatter['meta_description'])->toBe('Custom desc.')
        ->and($frontmatter)->toHaveKey('created_at');
});

it('marks private articles with status private in frontmatter', function (): void {
    $article = Article::factory()->draft()->create(['slug' => 'draft-article']);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['status'])->toBe('private');
});

it('omits null frontmatter fields', function (): void {
    $article = Article::factory()->published()->create([
        'summary' => null,
        'category_id' => null,
        'meta' => null,
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('description')
        ->and($frontmatter)->not->toHaveKey('category')
        ->and($frontmatter)->not->toHaveKey('meta_title')
        ->and($frontmatter)->not->toHaveKey('meta_description')
        ->and($frontmatter)->not->toHaveKey('last_edited_at')
        ->and($frontmatter)->not->toHaveKey('featured_image_url')
        ->and($frontmatter)->not->toHaveKey('featured_image_alt');
});

it('omits author from exported frontmatter', function (): void {
    $article = Article::factory()->published()->create();
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('author');
});

it('exports last_edited_at when set and omits it when null', function (): void {
    $articleWithEdit = Article::factory()->published()->create([
        'last_edited_at' => now()->subDay(),
    ]);
    $articleWithEdit->load('user', 'category', 'featuredPhoto.media');

    $articleNoEdit = Article::factory()->published()->create([
        'last_edited_at' => null,
    ]);
    $articleNoEdit->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();

    expect($service->buildFrontmatter($articleWithEdit))->toHaveKey('last_edited_at')
        ->and($service->buildFrontmatter($articleNoEdit))->not->toHaveKey('last_edited_at');
});

it('always exports created_at', function (): void {
    $article = Article::factory()->published()->create();
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->toHaveKey('created_at')
        ->and($frontmatter['created_at'])->toBe($article->created_at->utc()->toIso8601String());
});

it('omits empty past_slugs from frontmatter', function (): void {
    $article = Article::factory()->published()->create(['past_slugs' => []]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('past_slugs');
});

it('exports featured_image_url from external_featured_img_url column', function (): void {
    $article = Article::factory()->published()->create([
        'external_featured_img_url' => 'https://example.com/image.jpg',
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['featured_image_url'])->toBe('https://example.com/image.jpg');
});

it('does not use title as fallback for featured_image_alt', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'My Article Title',
        'photo_id' => null,
        'meta' => [],
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    // featured_image_alt must not fall back to title — it should be absent entirely
    expect($frontmatter)->not->toHaveKey('featured_image_alt');
});

it('exports featured_image_alt from meta when set', function (): void {
    $article = Article::factory()->published()->create([
        'meta' => ['featured_image_alt' => 'A descriptive alt text'],
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['featured_image_alt'])->toBe('A descriptive alt text');
});

it('omits featured_image_url when only a photo_id is set and no meta URL', function (): void {
    $article = Article::factory()->published()->create([
        'meta' => [],
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('featured_image_url');
});

it('zip contains categories.yaml', function (): void {
    Category::factory()->count(2)->create();

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new ArticleExportService();
    $service->streamCategoriesToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('categories.yaml');
    $za->close();
    unlink($tmpFile);

    expect($contents)->not->toBeFalse();
});

it('categories.yaml lists categories with correct slug and name', function (): void {
    $parent = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'PHP', 'slug' => 'php']);

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new ArticleExportService();
    $service->streamCategoriesToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('categories.yaml');
    $za->close();
    unlink($tmpFile);

    $data = Yaml::parse($yamlContent);

    $parentEntry = collect($data)->firstWhere('slug', 'technology');
    $childEntry = collect($data)->firstWhere('slug', 'php');

    expect($parentEntry)->not->toBeNull()
        ->and($parentEntry['name'])->toBe('Technology')
        ->and($parentEntry['parent_slug'])->toBeNull()
        ->and($childEntry)->not->toBeNull()
        ->and($childEntry['name'])->toBe('PHP')
        ->and($childEntry['parent_slug'])->toBe('technology');
});

it('zip contains settings.yaml', function (): void {
    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new ArticleExportService();
    $service->streamSettingsToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('settings.yaml');
    $za->close();
    unlink($tmpFile);

    expect($contents)->not->toBeFalse();
});

it('settings.yaml includes expected keys from settings and user', function (): void {
    $this->user->update(['name' => 'Jane Doe']);
    App\Models\Setting::set('theme_light', 'lofi');
    App\Models\Setting::set('page_home_subtitle', 'Welcome!');
    App\Models\Setting::set('profile_bio', 'A test bio.');

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new ArticleExportService();
    $service->streamSettingsToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('settings.yaml');
    $za->close();
    unlink($tmpFile);

    $data = Yaml::parse($yamlContent);

    expect($data['profile_name'])->toBe('Jane Doe')
        ->and($data['theme_light'])->toBe('lofi')
        ->and($data['page_home_subtitle'])->toBe('Welcome!')
        ->and($data['profile_bio'])->toBe('A test bio.');
});

it('exports photo_slug in frontmatter when article has a featured photo', function (): void {
    $photo = Photo::factory()->create(['slug' => 'my-photo']);
    $article = Article::factory()->published()->create([
        'slug' => 'photo-article',
        'photo_id' => $photo->id,
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter['photo_slug'])->toBe('my-photo');
});

it('omits photo_slug from frontmatter when article has no featured photo', function (): void {
    $article = Article::factory()->published()->create(['photo_id' => null]);
    $article->load('user', 'category', 'featuredPhoto.media');

    $service = new ArticleExportService();
    $frontmatter = $service->buildFrontmatter($article);

    expect($frontmatter)->not->toHaveKey('photo_slug');
});

it('zip contains photos.yaml', function (): void {
    Photo::factory()->count(2)->create();

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new PhotoExportService();
    $service->streamPhotosToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('photos.yaml');
    $za->close();
    unlink($tmpFile);

    expect($contents)->not->toBeFalse();
});

it('photos.yaml contains expected photo fields', function (): void {
    $category = Category::factory()->create(['slug' => 'landscape']);
    $photo = Photo::factory()->withCategory($category)->create([
        'slug' => 'sunset-photo',
        'caption' => 'A beautiful sunset.',
        'alt_text' => 'Sunset over the hills',
        'status' => Status::Public,
    ]);

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new PhotoExportService();
    $service->streamPhotosToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('photos.yaml');
    $za->close();
    unlink($tmpFile);

    $data = Yaml::parse($yamlContent);
    $entry = collect($data)->firstWhere('slug', 'sunset-photo');

    expect($entry)->not->toBeNull()
        ->and($entry['caption'])->toBe('A beautiful sunset.')
        ->and($entry['alt_text'])->toBe('Sunset over the hills')
        ->and($entry['status'])->toBe('public')
        ->and($entry['category'])->toBe('landscape');
});

it('images/ directory contains photo image files when photos have media', function (): void {
    Storage::fake('public');
    $photo = Photo::factory()->create(['slug' => 'with-image']);

    // Attach a fake image via MediaLibrary
    $fakeFile = UploadedFile::fake()->image('test-photo.jpg', 100, 100);
    $photo->addMedia($fakeFile->getRealPath())
        ->usingFileName('test-photo.jpg')
        ->toMediaCollection('image', 'public');

    $photo->load('media');

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new PhotoExportService();
    $service->streamPhotoImagesToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);

    $found = false;
    for ($i = 0; $i < $za->numFiles; $i++) {
        $name = $za->getNameIndex($i);
        if ($name === 'images/with-image.jpg') {
            $found = true;
            break;
        }
    }
    $za->close();
    unlink($tmpFile);

    expect($found)->toBeTrue();
});

it('photos.yaml includes image_file key for photos with media', function (): void {
    Storage::fake('public');
    $photo = Photo::factory()->create(['slug' => 'has-media']);

    $fakeFile = UploadedFile::fake()->image('my-image.jpg', 100, 100);
    $photo->addMedia($fakeFile->getRealPath())
        ->usingFileName('my-image.jpg')
        ->toMediaCollection('image', 'public');

    $photo->load('media');

    $stream = fopen('php://memory', 'r+');
    $zip = new ZipStream(outputName: null, sendHttpHeaders: false, outputStream: $stream);

    $service = new PhotoExportService();
    $service->streamPhotosToZip($zip);
    $zip->finish();

    rewind($stream);
    $zipBytes = stream_get_contents($stream);
    fclose($stream);

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('photos.yaml');
    $za->close();
    unlink($tmpFile);

    $data = Yaml::parse($yamlContent);
    $entry = collect($data)->firstWhere('slug', 'has-media');

    expect($entry)->toHaveKey('image_file')
        ->and($entry['image_file'])->toBe('has-media.jpg');
});

it('streams articles as markdown files to a zip', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'content' => 'Some content.',
    ]);
    $article->load('user', 'category', 'featuredPhoto.media');

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

// --- Single article export ---

it('exports a single article as a downloadable zip', function (): void {
    $article = Article::factory()->published()->create(['slug' => 'single-export']);

    $response = $this->get(route('admin.articles.export', $article));

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/zip');
    expect($response->headers->get('Content-Disposition'))->toContain('single-export-export-');
});

it('redirects unauthenticated users from single article export', function (): void {
    auth()->logout();

    $article = Article::factory()->published()->create();

    $this->get(route('admin.articles.export', $article))
        ->assertRedirect(route('login'));
});

it('single article zip contains the article markdown', function (): void {
    $article = Article::factory()->published()->create([
        'title' => 'Export Me',
        'slug' => 'export-me',
        'content' => 'Body content here.',
    ]);

    $response = $this->get(route('admin.articles.export', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $contents = $za->getFromName('articles/export-me.md');
    $za->close();
    unlink($tmpFile);

    expect($contents)
        ->toContain('title: \'Export Me\'')
        ->toContain('slug: export-me')
        ->toContain('Body content here.');
});

it('includes categories.yaml with ancestor chain when article has a category', function (): void {
    $parent = Category::factory()->create(['name' => 'Tech', 'slug' => 'tech']);
    $child = Category::factory()->withParent($parent)->create(['name' => 'PHP', 'slug' => 'php']);
    $article = Article::factory()->published()->create([
        'slug' => 'cat-article',
        'category_id' => $child->id,
    ]);

    $response = $this->get(route('admin.articles.export', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $yamlContent = $za->getFromName('categories.yaml');
    $za->close();
    unlink($tmpFile);

    $data = Yaml::parse($yamlContent);

    expect($data)->toHaveCount(2);

    $techEntry = collect($data)->firstWhere('slug', 'tech');
    $phpEntry = collect($data)->firstWhere('slug', 'php');

    expect($techEntry['name'])->toBe('Tech')
        ->and($techEntry['parent_slug'])->toBeNull()
        ->and($phpEntry['name'])->toBe('PHP')
        ->and($phpEntry['parent_slug'])->toBe('tech');
});

it('omits categories.yaml when article has no category', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'no-cat',
        'category_id' => null,
    ]);

    $response = $this->get(route('admin.articles.export', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $result = $za->getFromName('categories.yaml');
    $za->close();
    unlink($tmpFile);

    expect($result)->toBeFalse();
});

it('includes photos.yaml and image when article has a featured photo', function (): void {
    Storage::fake('public');

    $photo = Photo::factory()->create(['slug' => 'featured-img']);
    $fakeFile = UploadedFile::fake()->image('featured.jpg', 100, 100);
    $photo->addMedia($fakeFile->getRealPath())
        ->usingFileName('featured.jpg')
        ->toMediaCollection('image', 'public');

    $article = Article::factory()->published()->create([
        'slug' => 'with-photo',
        'photo_id' => $photo->id,
    ]);

    $response = $this->get(route('admin.articles.export', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $photosYaml = $za->getFromName('photos.yaml');
    $imageFile = $za->getFromName('images/featured-img.jpg');
    $za->close();
    unlink($tmpFile);

    expect($photosYaml)->not->toBeFalse();

    $data = Yaml::parse($photosYaml);
    expect($data)->toHaveCount(1)
        ->and($data[0]['slug'])->toBe('featured-img');

    expect($imageFile)->not->toBeFalse();
});

it('omits photos.yaml when article has no featured photo', function (): void {
    $article = Article::factory()->published()->create([
        'slug' => 'no-photo',
        'photo_id' => null,
    ]);

    $response = $this->get(route('admin.articles.export', $article));
    $zipBytes = $response->streamedContent();

    $tmpFile = tempnam(sys_get_temp_dir(), 'bw-test-');
    file_put_contents($tmpFile, $zipBytes);

    $za = new ZipArchive();
    $za->open($tmpFile, ZipArchive::RDONLY);
    $result = $za->getFromName('photos.yaml');
    $za->close();
    unlink($tmpFile);

    expect($result)->toBeFalse();
});
