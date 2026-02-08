# Themes

A theme is a folder of HTML templates that controls how your blog looks. BlogWriter handles all the data — fetching posts, generating feeds, managing the admin — your theme just decides how to display it.

You don't need to be a professional developer to build a theme. If you can edit HTML and add CSS classes, you can customize your blog's appearance.

## Theme Structure

Every theme lives in the `themes/` directory at the root of your BlogWriter installation:

```
themes/
└── my-theme/
    ├── theme.json                          # Theme metadata
    ├── pages/                              # Page templates
    │   ├── index.blade.php                 # Homepage
    │   ├── blog/
    │   │   └── [Article:slug].blade.php    # Single article
    │   ├── notes/
    │   │   └── [Note:uuid].blade.php       # Single note
    │   ├── photos/
    │   │   └── [Photo:id].blade.php        # Single photo
    │   ├── category/
    │   │   └── [Category:slug].blade.php   # Category archive
    │   └── tag/
    │       └── [Tag:slug].blade.php        # Tag archive
    └── components/                         # Optional: override built-in components
        └── article-card.blade.php          # Custom version of a built-in component
```

### How Routing Works

File names in `pages/` map directly to URLs. Your file structure *is* your URL structure:

| File                                  | URL                            |
|---------------------------------------|--------------------------------|
| `pages/index.blade.php`               | `yourdomain.com/`              |
| `pages/blog/[Article:slug].blade.php` | `yourdomain.com/blog/my-post`  |
| `pages/notes/[Note:uuid].blade.php`   | `yourdomain.com/notes/abc-123` |
| `pages/photos/[Photo:id].blade.php`   | `yourdomain.com/photos/42`     |
| `pages/category/[Category:slug].blade.php` | `yourdomain.com/category/tech` |
| `pages/tag/[Tag:slug].blade.php`      | `yourdomain.com/tag/laravel`   |

> **For Developers:** BlogWriter uses Laravel Folio under the hood. The brackets (`[Article:slug]`) tell Folio to look up the model by that field — no routing code needed.

### theme.json

Every theme needs a `theme.json` file:

```json
{
    "name": "My Theme",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "A clean, minimal blog theme",
    "screenshot": "screenshot.png"
}
```

| Field         | Required | Description                                      |
|---------------|----------|--------------------------------------------------|
| `name`        | Yes      | Display name shown in Settings                   |
| `version`     | Yes      | Semantic version (1.0.0)                         |
| `author`      | Yes      | Your name or handle                              |
| `description` | No       | Short description of the theme                   |
| `screenshot`  | No       | Path to a preview image (relative to theme root) |

---

## Included Themes

BlogWriter ships with two themes:

**Terminal** — The default theme with a retro terminal aesthetic. Active after installation.

**Starter** — A minimalist theme designed as a foundation for building your own designs. Uses Tailwind CSS + DaisyUI loaded via CDN (no build step, no npm, no Node.js). Includes all required page templates with clean, minimal styling you can build on.

> **For Developers:** The Starter theme is similar to WordPress's _underscores — a barebones starting point rather than a finished design.

### Switching Themes

1. Go to **Settings > Theme** in the admin dashboard
2. Select a theme from the list
3. Visit your site — changes are live immediately

### Installing Additional Themes

**Upload a theme:**
1. Go to **Settings > Theme** in the admin
2. Click **Upload Theme**
3. Drag and drop a `.zip` file or click to browse
4. The theme installs automatically and appears in your theme list

**Or use the command line:**
```bash
php artisan theme:install theme-name
```

---

## Building Your Own Theme

To build a theme, you'll need a local installation of BlogWriter where you can test your changes. Once your theme is ready, zip it up and upload it to your live site (or share it with others).

> **For Developers:** Setting up a local development environment is covered in the Architecture doc. Non-developers can also request a development environment setup guide in the community forums.

### 1. Copy the Starter Theme

In your BlogWriter installation's `themes/` folder, duplicate the `starter` theme:

### 2. Edit theme.json

Open `themes/my-theme/theme.json` and change the name:

```json
{
    "name": "My Theme",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "My custom BlogWriter theme"
}
```

### 3. Edit the Templates

Open the files in `pages/` and edit the HTML. The templates use Blade syntax (Laravel's templating language), but you
mostly just need to know a few things:

**Outputting data:**

```html
<!-- Display a variable -->
<h1>{{ $article->title }}</h1>

<!-- Display raw HTML (for rendered content) -->
{!! $article->content_html !!}
```

**Loops:**

```html
@foreach ($articles as $article)
<div class="card">
    <h2>{{ $article->title }}</h2>
</div>
@endforeach
```

**Conditionals:**

```html
@if ($article->summary)
<p>{{ $article->summary }}</p>
@endif
```

That's most of what you'll need.

### 4. Style with Tailwind

The starter theme loads Tailwind + DaisyUI via CDN, so you can use any Tailwind class directly in your HTML:

```html

<article class="max-w-prose mx-auto py-8">
    <h1 class="text-3xl font-bold mb-4">{{ $article->title }}</h1>
    <p class="text-gray-500 text-sm">{{ $article->published_at->format('F j, Y') }}</p>
    <div class="prose mt-6">
        {!! $article->content_html !!}
    </div>
</article>
```

No build step. Edit, save, refresh.

### 5. Activate Your Theme

Go to **Settings > Theme** in the admin and select your theme.

---

## Getting Started: Developer Path

Same theme structure, but you can add your own tooling.

### Custom Build Process

If you want to use Vite, PostCSS, or any other build tool:

1. Add a `package.json` to your theme folder
2. Set up your build pipeline to output CSS/JS into a `dist/` folder in your theme
3. Reference those files in your layout template
4. Ship compiled assets — BlogWriter doesn't run your build for you

### Custom CSS Instead of CDN

Replace the CDN `<link>` tags in your layout with your own compiled stylesheet:

```html

<link rel="stylesheet" href="/themes/my-theme/dist/styles.css">
```

### Publishing Themes

[TODO: Theme marketplace/distribution details]

---

## Data Available in Templates

### On Every Page

Your templates always have access to site and author information:

```html
<!-- Site info -->
{{ site('name') }}
{{ site('tagline') }}
{{ site('domain') }}

<!-- Author info -->
{{ author('name') }}
{{ author('bio') }}
{{ author('avatar') }}
{{ author('email') }}
```

### Homepage (`index.blade.php`)

The homepage receives a mixed collection of recent posts (articles, notes, photos), sorted by publish date:

```php
@php
    use App\Models\Article;
    use App\Models\Note;
    use App\Models\Photo;

    // Recent posts across all types
    $articles = Article::published()->latest('published_at')->limit(10)->get();
    $notes = Note::published()->latest('published_at')->limit(10)->get();
    $photos = Photo::published()->latest('published_at')->limit(10)->get();
@endphp
```

### Article Page (`[Article:slug].blade.php`)

Folio automatically resolves the `$article` variable from the URL slug. Available properties:

| Property                     | Type         | Description                                    |
|------------------------------|--------------|------------------------------------------------|
| `$article->title`            | string       | The article title                              |
| `$article->slug`             | string       | URL slug                                       |
| `$article->summary`          | string\|null | Short description                              |
| `$article->content_html`     | string       | Rendered HTML content                          |
| `$article->content_markdown` | string       | Raw Markdown                                   |
| `$article->published_at`     | Carbon       | Publish date                                   |
| `$article->status`           | string       | "draft" or "published"                         |
| `$article->reading_time`     | int          | Estimated reading time in minutes              |
| `$article->word_count`       | int          | Word count                                     |
| `$article->categories`       | Collection   | Associated categories                          |
| `$article->tags`             | Collection   | Associated tags                                |
| `$article->meta`             | array        | Additional metadata (reading_time, word_count) |

### Note Page (`[Note:uuid].blade.php`)

| Property              | Type          | Description            |
|-----------------------|---------------|------------------------|
| `$note->id`           | string (UUID) | Unique identifier      |
| `$note->content`      | string        | Raw Markdown           |
| `$note->content_html` | string        | Rendered HTML          |
| `$note->published_at` | Carbon        | Publish date           |
| `$note->status`       | string        | "draft" or "published" |
| `$note->tags`         | Collection    | Associated tags        |

### Photo Page (`[Photo:id].blade.php`)

| Property               | Type         | Description                                    |
|------------------------|--------------|------------------------------------------------|
| `$photo->id`           | int          | Numeric ID                                     |
| `$photo->filename`     | string       | Original filename                              |
| `$photo->path`         | string       | Storage path or URL                            |
| `$photo->caption`      | string\|null | Photo caption (Markdown)                       |
| `$photo->alt_text`     | string\|null | Alt text for accessibility                     |
| `$photo->published_at` | Carbon       | Publish date                                   |
| `$photo->status`       | string       | "draft" or "published"                         |
| `$photo->meta`         | array        | EXIF data (camera, date, location, dimensions) |
| `$photo->tags`         | Collection   | Associated tags                                |

### Category Page (`[Category:slug].blade.php`)

| Property                 | Type         | Description               |
|--------------------------|--------------|---------------------------|
| `$category->name`        | string       | Category name             |
| `$category->slug`        | string       | URL slug                  |
| `$category->description` | string\|null | Category description      |
| `$category->articles`    | Collection   | Articles in this category |

### Tag Page (`[Tag:slug].blade.php`)

| Property         | Type       | Description            |
|------------------|------------|------------------------|
| `$tag->name`     | string     | Tag name               |
| `$tag->slug`     | string     | URL slug               |
| `$tag->articles` | Collection | Articles with this tag |
| `$tag->notes`    | Collection | Notes with this tag    |
| `$tag->photos`   | Collection | Photos with this tag   |

---

## Built-In Components

BlogWriter includes ready-to-use Blade components for common elements like article cards, navigation, author bios, and more. You can use them as-is or override them with your own versions.

### Using Built-In Components

Just reference them in your templates:

```html
<x-article-card :article="$article" />
<x-navbar />
<x-author-bio />
```

### Overriding Components

To customize a component, create a file with the same name in your theme's `components/` directory. Your version will be used instead of the built-in one.

For example, to override the article card:
1. Create `themes/my-theme/components/article-card.blade.php`
2. Copy the markup from the [Components](05_components.md) doc
3. Modify it however you want

See the [Components](05_components.md) doc for a complete list of available components with copyable code.

---

## Tips

- **Start with the starter theme.** Don't build from scratch unless you have a reason.
- **Use built-in components.** Override only what you need to customize.
- **Check your microformats.** The built-in components include proper microformats markup. If you build custom templates, see the [Feeds & IndieWeb](06_feeds-and-indieweb.md) doc.
- **Test with real content.** BlogWriter's seeders create sample articles, notes, and photos so you can see how your theme handles real data.

#### [Up Next: *Components*](05_components.md)
