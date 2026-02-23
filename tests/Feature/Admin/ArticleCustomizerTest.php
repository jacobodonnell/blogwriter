<?php

declare(strict_types=1);

use App\Enums\Status;
use App\Models\Article;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('displays customizer layout for edit page', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.customizer');
});

it('shows full-page preview for draft articles', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.show', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.preview-fullscreen')
        ->assertSee('Preview Mode');
});

it('shows full-page preview for published articles', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.show', $article))
        ->assertOk()
        ->assertViewIs('admin.articles.preview-fullscreen')
        ->assertSee('Preview Mode');
});

it('requires auth for preview', function (): void {
    auth()->logout();

    $article = Article::factory()->draft()->create();

    get(route('admin.articles.show', $article))
        ->assertRedirect();
});

it('returns preview partial for ajax preview update', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Original Title',
        'content' => 'Original content',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content here',
        'status' => Status::Draft->value,
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview')
        ->assertSee('Updated Title');

    // Live columns remain untouched
    $fresh = $article->fresh();
    expect($fresh->title)->toBe('Original Title')
        ->and($fresh->getRawOriginal('content'))->toBe('Original content');
});

it('redirects normally for full save update requests', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => Status::Draft->value,
    ])
        ->assertRedirect(route('admin.articles.edit', $article))
        ->assertSessionHas('success');
});

it('rejects update with empty content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => '',
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});

it('rejects update with missing content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});

it('preview update accepts relaxed validation', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Original',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'Hi',
        'slug' => 'untitled-abcd1234',
        'status' => Status::Draft->value,
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview');
});

it('preview update auto-generates slug from title when placeholder', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'My Great Post',
        'slug' => 'untitled-abcd1234',
        'status' => Status::Draft->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->draft['slug'])->toBe('my-great-post')
        ->and($fresh->getRawOriginal('slug'))->toBe('untitled-abcd1234');
});

it('preview update uniquifies slug when it conflicts with another article', function (): void {
    Article::factory()->published()->for($this->user)->create([
        'slug' => 'there',
    ]);

    $article = Article::factory()->draft()->for($this->user)->create([
        'title' => 'Untitled Article',
        'slug' => 'untitled-abcd1234',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'There',
        'slug' => 'untitled-abcd1234',
        'status' => Status::Draft->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->draft['slug'])->toBe('there-1')
        ->and($fresh->draft['title'])->toBe('There')
        ->and($fresh->getRawOriginal('title'))->toBe('Untitled Article');
});

it('full save preserves existing featured image', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'meta' => ['featured_image_url' => 'https://example.com/image.jpg'],
    ]);

    put(route('admin.articles.update', $article), [
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    expect($article->fresh()->meta['featured_image_url'])->toBe('https://example.com/image.jpg');
});

it('index view button links to preview for drafts', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'published_at' => null,
    ]);

    get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee(route('admin.articles.show', $article))
        ->assertSee('Preview Draft');
});

it('accepts POST with _method PUT for ajax preview update', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    post(route('admin.articles.preview.update', $article), [
        '_method' => 'PUT',
        'title' => 'Updated Title',
        'slug' => $article->slug,
        'content' => 'Updated content',
        'status' => Status::Draft->value,
    ])
        ->assertOk()
        ->assertViewIs('admin.articles.preview');
});

it('renders view live link for published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('View Live');
});

it('does not render view live link for draft article', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertDontSee('View Live');
});

it('index view button links to permalink for published', function (): void {
    $article = Article::factory()->published()->for($this->user)->create();

    get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee($article->permalink())
        ->assertSee('View Published');
});

it('stores content submitted from tiptap editor', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Tiptap Article',
        'slug' => $article->slug,
        'content' => "## Hello\n\nThis is a paragraph.",
        'status' => Status::Draft->value,
    ])->assertRedirect(route('admin.articles.edit', $article));

    expect($article->fresh()->content)->toBe("## Hello\n\nThis is a paragraph.");
});

it('stores bold and italic markdown content correctly', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Formatting Test',
        'slug' => $article->slug,
        'content' => '**bold** and _italic_ text',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $content = $article->fresh()->content;

    expect($content)->toContain('**bold**')
        ->and($content)->toContain('_italic_');
});

it('rejects content containing an H1 heading submitted from editor', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'H1 Bypass Test',
        'slug' => $article->slug,
        'content' => "# Top level heading\n\nContent",
        'status' => Status::Draft->value,
    ])->assertSessionHasErrors('content');
});

it('stores blockquote markdown correctly', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Blockquote Test',
        'slug' => $article->slug,
        'content' => '> This is a quote',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    expect($article->fresh()->content)->toContain('> This is a quote');
});

it('stores link markdown correctly', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    put(route('admin.articles.update', $article), [
        'title' => 'Link Test',
        'slug' => $article->slug,
        'content' => '[Visit example](https://example.com)',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    expect($article->fresh()->content)->toContain('[Visit example](https://example.com)');
});

// ---------------------------------------------------------------------------
// Draft snapshot system (JSON draft column)
// ---------------------------------------------------------------------------

it('preview update writes to draft JSON, not live content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => 'Original content',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => 'Typed in editor',
        'status' => Status::Draft->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->getRawOriginal('content'))->toBe('Original content')
        ->and($fresh->draft['content'])->toBe('Typed in editor');
});

it('preview update does NOT overwrite live title on published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create([
        'title' => 'Live Title',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => 'Draft Title',
        'slug' => $article->slug,
        'content' => $article->content,
        'status' => Status::Published->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->title)->toBe('Live Title')
        ->and($fresh->draft['title'])->toBe('Draft Title');
});

it('preview update does NOT overwrite live category on published article', function (): void {
    $category = App\Models\Category::factory()->create();
    $newCategory = App\Models\Category::factory()->create();

    $article = Article::factory()->published()->for($this->user)->create([
        'category_id' => $category->id,
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'category_id' => $newCategory->id,
        'status' => Status::Published->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->category_id)->toBe($category->id)
        ->and($fresh->draft['category_id'])->toBe($newCategory->id);
});

it('preview update does NOT overwrite live meta on published article', function (): void {
    $article = Article::factory()->published()->for($this->user)->create([
        'meta' => ['meta_title' => 'Live SEO Title'],
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'meta' => ['meta_title' => 'Draft SEO Title'],
        'status' => Status::Published->value,
    ])->assertOk();

    $fresh = $article->fresh();
    expect($fresh->meta['meta_title'])->toBe('Live SEO Title')
        ->and($fresh->draft['meta']['meta_title'])->toBe('Draft SEO Title');
});

it('full save promotes draft content and clears draft', function (): void {
    $article = Article::factory()->draft()->for($this->user)->withDraft([
        'content' => 'Staged draft edits',
    ])->create([
        'content' => 'Published content',
    ]);

    put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => 'Staged draft edits',
        'status' => Status::Draft->value,
    ])->assertRedirect(route('admin.articles.edit', $article));

    $fresh = $article->fresh();
    expect($fresh->content)->toBe('Staged draft edits')
        ->and($fresh->draft)->toBeNull();
});

it('full save without prior draft uses submitted content', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => 'Old content',
    ]);

    put(route('admin.articles.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => 'Brand new content',
        'status' => Status::Draft->value,
    ])->assertRedirect();

    $fresh = $article->fresh();
    expect($fresh->content)->toBe('Brand new content')
        ->and($fresh->draft)->toBeNull();
});

it('edit page initialises editor with draft content when present', function (): void {
    $article = Article::factory()->draft()->for($this->user)->withDraft([
        'content' => 'Draft version in editor',
    ])->create([
        'content' => 'Published version',
    ]);

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('Draft version in editor', escape: false);
});

it('edit page shows draft title when draft exists', function (): void {
    $article = Article::factory()->draft()->for($this->user)->withDraft([
        'title' => 'Draft Title Override',
    ])->create([
        'title' => 'Original Title',
    ]);

    get(route('admin.articles.edit', $article))
        ->assertOk()
        ->assertSee('Draft Title Override');
});

it('hasDraft returns true when draft JSON is present', function (): void {
    $article = Article::factory()->draft()->for($this->user)->withDraft()->create();

    expect($article->fresh()->hasDraft())->toBeTrue();
});

it('hasDraft returns false when draft is null', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create();

    expect($article->fresh()->hasDraft())->toBeFalse();
});

it('preview renders draft content in preview pane', function (): void {
    $article = Article::factory()->draft()->for($this->user)->create([
        'content' => 'Original content here',
    ]);

    put(route('admin.articles.preview.update', $article), [
        'title' => $article->title,
        'slug' => $article->slug,
        'content' => 'Brand new draft text',
        'status' => Status::Draft->value,
    ])
        ->assertOk()
        ->assertSee('Brand new draft text');
});
