---
title: Architecture
description: Technical overview of BlogWriter's stack, design decisions, and contributor guidelines.
extends: _layouts.documentation
section: content
category: advanced
order: 8
---

# Architecture

A technical overview for contributors. If you want to hack on BlogWriter's core, this is your map.

---

## Philosophy

BlogWriter is built on a few core principles:

**Simple by default.** SQLite instead of MySQL. Alpine.js instead of a heavy JavaScript framework. One user instead of
multi-tenancy. Less is more.

**Own your stack.** No SaaS dependencies. No external APIs for core features. Everything runs on your server.

**Standards over platforms.** RSS, microformats, IndieAuth — protocols that have worked for decades and will work for
decades more.

---

## Tech Stack

| Layer             | Technology              | Version | Why                                   |
|-------------------|-------------------------|---------|---------------------------------------|
| Backend           | Laravel                 | 12      | Modern PHP framework with great DX    |
| Language          | PHP                     | 8.4+    | Ubiquitous, runs anywhere             |
| Database          | SQLite                  | —       | Single file, zero config, fast enough |
| Frontend (public) | Alpine.js + Alpine AJAX | v3      | Reactive without the complexity       |
| Frontend (admin)  | Alpine.js + Alpine AJAX | v3      | Same stack everywhere                 |
| CSS               | Tailwind CSS            | v4      | Utility-first, no custom CSS needed   |
| CSS Components    | DaisyUI                 | v5      | Pre-built components, CDN-friendly    |
| Auth Backend      | Laravel Fortify         | v1      | Headless auth, bring your own UI      |
| Auth UI           | Alpine AJAX + DaisyUI   | —       | Custom built, not Livewire            |
| Theme Routing     | Laravel Folio           | —       | File-based routing for themes         |
| Rich Text         | Editor.js               | v2      | Block-based editor, clean JSON output |
| Testing           | Pest                    | v4      | Expressive, readable tests            |

---

## Key Architectural Decisions

### SQLite Only

BlogWriter uses SQLite as its only supported database. No MySQL, no Postgres, no choice.

**Why:**

- **Simplicity** — Database is a single file. No separate server process.
- **Portability** — Copy the file, you've copied the database.
- **Hosting** — Works on cheap shared hosting without database server access.
- **Performance** — More than fast enough for a single-author blog.
- **Reliability** — Fewer moving parts means fewer failure modes.

For a personal blog, SQLite is the right choice. We're not building Twitter.

### Alpine.js Everywhere (No Livewire)

The entire application — admin and public — uses Alpine.js for interactivity. No Livewire, no Vue, no React.

**Why:**

- **Consistency** — Same mental model everywhere.
- **Simplicity** — Alpine is just HTML with sprinkles of JavaScript.
- **Performance** — No server-side component state to manage.
- **Progressive enhancement** — Works without JavaScript, better with it.

Alpine AJAX handles dynamic updates (auto-save, search, form submissions) without page reloads. Standard Laravel
controllers handle the backend. Clean separation.

### Custom Auth UI (Not Livewire Starter Kit)

Authentication uses Laravel Fortify for the backend, but the UI is custom-built with Alpine AJAX and DaisyUI forms. No
Livewire starter kit.

**Why:**

- **Control** — We own the entire auth flow.
- **Consistency** — Same Alpine + DaisyUI stack as the rest of the admin.
- **Simplicity** — Standard forms posting to Fortify routes. No magic.

### Folio for Theme Routing

Themes use file-based routing via Laravel Folio. File structure = URL structure.

**Example:**

```
pages/blog/[Article:slug].blade.php → /blog/my-article
pages/notes/[Note:uuid].blade.php   → /notes/abc-123
pages/photos/[Photo:id].blade.php   → /photos/42
```

**Why:**

- **Intuitive** — Theme creators don't need to understand routing.
- **Convention** — Put the file in the right place, it works.
- **Automatic model binding** — `[Article:slug]` loads the article by slug.

### Single-Author Only

One admin account. One blog. No user roles, no multi-tenancy, no permissions system.

**Why:**

- **Scope** — This is for personal blogs, not CMSs.
- **Simplicity** — No role-based access control complexity.
- **Security** — Smaller attack surface.

If you need multi-author, WordPress exists.

### Editor.js for Articles

Articles use Editor.js, a block-based editor that outputs clean JSON.

**Why:**

- **Modern UX** — Blocks you can drag, reorder, and nest.
- **Structured data** — JSON makes it easy to render, transform, export.
- **Extensible** — Plugin architecture for custom blocks.

Notes use a simpler inline rich text editor (same block system, fewer options). Photos are just caption + image.

### Component Override System

BlogWriter ships with built-in Blade components (`resources/views/components/`). Themes can override them by creating a
component with the same name in `themes/your-theme/components/`.

**Lookup order:**

1. `themes/active-theme/components/article-card.blade.php`
2. `resources/views/components/article-card.blade.php`

**Why:**

- **Customization** — Override only what you need.
- **Maintenance** — Most themes use built-in components, get updates for free.
- **DRY** — Common components shared across themes.

---

## Directory Structure

```
blogwriter/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ArticleController.php       # Public article display
│   │   │   ├── NoteController.php
│   │   │   ├── PhotoController.php
│   │   │   ├── FeedController.php          # RSS/Atom/JSON feeds
│   │   │   ├── Admin/
│   │   │   │   ├── ArticleController.php   # Admin CRUD
│   │   │   │   ├── NoteController.php
│   │   │   │   ├── PhotoController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── TagController.php
│   │   │   │   └── SettingsController.php
│   │   │   └── Auth/
│   │   │       └── # Custom Alpine-based auth controllers
│   │   ├── Requests/
│   │   │   ├── StoreArticleRequest.php
│   │   │   ├── UpdateArticleRequest.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       └── EnsureInstalled.php          # Redirect to installer if needed
│   ├── Models/
│   │   ├── Article.php
│   │   ├── Note.php
│   │   ├── Photo.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   └── User.php
│   ├── View/
│   │   └── Components/
│   │       └── # View composers, not Livewire components
│   └── Console/
│       └── Commands/
│           ├── InstallCommand.php           # php artisan blogwriter:install
│           └── ThemeInstallCommand.php
├── config/
│   └── blogwriter.php                       # Site/author config
├── database/
│   ├── database.sqlite                      # Created by installer
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── components/                      # Built-in components
│   │   │   ├── navbar.blade.php
│   │   │   ├── article-card.blade.php
│   │   │   ├── note-card.blade.php
│   │   │   ├── photo-card.blade.php
│   │   │   ├── h-card.blade.php
│   │   │   ├── h-entry-article.blade.php
│   │   │   └── ...
│   │   ├── admin/                           # Admin dashboard views
│   │   │   ├── layout.blade.php
│   │   │   ├── dashboard.blade.php
│   │   │   ├── articles/
│   │   │   ├── notes/
│   │   │   ├── photos/
│   │   │   └── settings/
│   │   ├── auth/                            # Custom auth UI
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   └── ...
│   │   └── installer/                       # Web installer views
│   │       └── ...
│   ├── css/
│   │   └── app.css                          # Tailwind entry point
│   └── js/
│       ├── app.js                           # Alpine.js setup
│       └── editor.js                        # Editor.js configuration
├── themes/
│   ├── terminal/                            # Default theme (active)
│   │   ├── theme.json
│   │   ├── pages/
│   │   │   ├── index.blade.php
│   │   │   ├── blog/
│   │   │   │   └── [Article:slug].blade.php
│   │   │   ├── notes/
│   │   │   │   └── [Note:uuid].blade.php
│   │   │   └── ...
│   │   └── components/                      # Optional theme overrides
│   │       └── article-card.blade.php
│   └── starter/                             # Starter theme (inactive)
│       └── ...
├── storage/
│   ├── app/
│   │   └── public/
│   │       ├── photos/                      # Uploaded photos
│   │       └── avatars/
│   └── backups/                             # Automatic markdown backups
│       └── articles/
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Models & Relationships

### Shared Post Behavior

Article, Note, and Photo share common patterns for publishing workflow:

**Common Fields:**

- `status` — 'draft' or 'published'
- `published_at` — nullable timestamp
- `meta` — JSON column for extended data

**Common Scopes:**

- `published()` — Only published posts
- `draft()` — Only drafts

**Common Methods:**

- `isPublished(): bool` — Check if post is live
- `permalink(): string` — Full URL to this post

**Relationships:**

- `tags()` — Polymorphic many-to-many

**Note:** These patterns are implemented directly in each model. Extract to a trait only when you see the same implementation three times.

### Article

**Fields:**

- `slug` (unique, immutable after publish)
- `title`
- `summary` (nullable)
- `content_json` (Editor.js blocks)
- `content_markdown` (auto-generated backup)
- `status` ('draft' or 'published')
- `published_at` (nullable timestamp)

**Relationships:**

- `belongsToMany(Category)`
- `morphToMany(Tag)`

**Accessors:**

- `content_html` — Renders Editor.js JSON to HTML

**Permalink:** `/blog/{slug}`

### Note

**Fields:**

- `id` (UUID primary key)
- `content` (rich text, stored as JSON)
- `status`
- `published_at`

**Relationships:**

- `morphToMany(Tag)`

**Accessors:**

- `content_html` — Renders JSON to HTML

**Permalink:** `/notes/{uuid}`

**Notes:**

- No draft system — published immediately
- No title field (titleless by design)
- Automatically backed up as Markdown files

### Photo

**Fields:**

- `filename`
- `path` (storage path)
- `caption` (rich text, optional)
- `alt_text`
- `mime_type`
- `size` (bytes)
- `meta` (JSON: EXIF data)
- `status`
- `published_at`

**Relationships:**

- `morphToMany(Tag)`

**Permalink:** `/photos/{id}`

**Storage:**

- Images stored as files in `storage/app/public/photos/`
- Database stores path, filename, and metadata
- EXIF data extracted and stored in `meta` JSON column

**EXIF in meta:**

```json
{
    "camera": "Canon EOS R5",
    "lens": "RF 50mm f/1.2",
    "iso": 400,
    "aperture": "f/1.8",
    "shutter": "1/200",
    "taken_at": "2026-01-31 14:23:00",
    "gps": {
        "lat": 45.5231,
        "lon": -122.6765
    },
    "dimensions": {
        "width": 8192,
        "height": 5464
    }
}
```

### Category

**Fields:**

- `name`
- `slug` (auto-generated from name)
- `description` (nullable)

**Relationships:**

- `belongsToMany(Article)`

**Note:** Categories are article-only. Notes and photos don't have categories.

### Tag

**Fields:**

- `name`
- `slug`

**Relationships:**

- `morphedByMany(Article)`
- `morphedByMany(Note)`
- `morphedByMany(Photo)`

**Note:** Tags work across all post types.

---

## Content Storage & Rendering

| Type    | Editor           | Storage                 | Rendering               | Export               |
|---------|------------------|-------------------------|-------------------------|----------------------|
| Article | Editor.js blocks | `content_json` (JSON)   | JSON → HTML on read     | Markdown backup      |
|         |                  | `content_markdown` (MD) | Markdown as fallback    | `.md` URL (optional) |
| Note    | Inline rich text | `content` (JSON)        | JSON → HTML on read     | `.md` URL (optional) |
| Photo   | Caption field    | `caption` (JSON)        | JSON → HTML for caption | Image file           |

### Automatic Markdown Backups

Articles and notes are automatically backed up as Markdown files:

```
storage/backups/articles/2026/01/my-article-slug.md
storage/backups/notes/2026/01/abc-123-uuid.md
```

**Article format:**

```markdown
---
title: My Article Title
date: 2026-01-31
slug: my-article-slug
categories: [Tech, Laravel]
tags: [php, blogging]
---

Article content in Markdown...
```

**Note format:**

```markdown
---
date: 2026-01-31 14:23:00
uuid: abc-123-uuid
tags: [quick-thought, web]
---

Note content in Markdown...
```

**Why:**

- **Portability** — Easy to migrate to another platform
- **Version control** — Can be committed to git
- **Recovery** — Database corrupted? You have the Markdown

Photos are stored as files (already portable), so no separate backup needed.

---

## Authentication Flow

### Backend: Laravel Fortify

Fortify provides routes and controllers:

- `/login` → POST to authenticate
- `/register` → POST to create user
- `/logout` → POST to destroy session
- `/password/reset` → Password reset flow
- `/email/verify` → Email verification

### Frontend: Custom Alpine UI

All auth views are custom-built with Alpine.js and DaisyUI. No Livewire.

**Example (login):**

```blade
<form x-data x-target="login-form" method="POST" action="/login">
    @csrf
    <input type="email" name="email" class="input input-bordered" />
    <input type="password" name="password" class="input input-bordered" />
    <button type="submit" class="btn btn-primary">Log In</button>
</form>
```

Alpine AJAX submits the form, Fortify validates and responds. On success, redirect. On error, show validation errors
inline.

---

## Feeds

**Controller:** `FeedController`

**How it works:**

1. Query last 50 published posts across all types
2. Order by `published_at DESC`
3. Pass to RSS/Atom/JSON Feed Blade views
4. Cache for 5 minutes
5. Invalidate cache when posts are created/updated/deleted

**Formats:**

- `/feed` (or `/rss`) → RSS 2.0
- `/atom` → Atom 1.0
- `/feed.json` → JSON Feed 1.1

All three include full content (not excerpts).

---

## Search

BlogWriter uses SQLite FTS5 (Full-Text Search) for content search:

**Implementation:**

- Virtual FTS table mirrors `articles`, `notes` tables
- Triggers keep FTS table in sync with content changes
- Search queries use `MATCH` against FTS table
- Results ranked by relevance

**Frontend:**

- Command Palette (Cmd/Ctrl+K) for fuzzy command search (client-side)
- Content search with debounced Alpine AJAX requests (server-side)
- Results update as you type (300ms debounce)

**Backend:**

- Rate limited to 60 requests/minute
- Results cached for 5 minutes per query
- Limited to 10 results per request

---

## Configuration & Settings

**Config file:** `config/blogwriter.php`

**Environment variables:**

```env
SITE_NAME="My Blog"
SITE_DOMAIN="https://blog.example.com"
SITE_TAGLINE="Thoughts on tech and life"

AUTHOR_NAME="Jane Smith"
AUTHOR_BIO="Writer and developer"
AUTHOR_AVATAR="/storage/avatar.jpg"
AUTHOR_EMAIL="jane@example.com"

MARKDOWN_EXPORT_ENABLED=true
```

**Access in views:**

```blade
{{ site('name') }}
{{ site('domain') }}
{{ author('name') }}
{{ author('bio') }}
```

**Update via admin:**
Settings page writes to `.env` file using `DotenvEditor`.

**Update via CLI:**

```bash
php artisan config:set blogwriter.site.name "New Name"
```

---

## Theme System

### How Themes Work

1. Active theme is stored in `config/blogwriter.php`
2. Routes are registered from `themes/{active}/pages/`
3. Components are loaded from theme first, fallback to built-in
4. CSS/JS can be theme-specific or use global DaisyUI CDN

### Component Override Priority

When rendering `<x-article-card>`:

1. Check `themes/active-theme/components/article-card.blade.php`
2. If not found, use `resources/views/components/article-card.blade.php`

This lets themes override specific components without copying everything.

### Installing Themes

**Via upload (admin):**

1. Upload `.zip` file
2. Extract to `themes/` directory
3. Validate `theme.json`
4. Theme appears in Settings > Theme

**Via CLI:**

```bash
php artisan theme:install theme-name
```

---

## Installation System

BlogWriter includes both web and CLI installers.

**Web installer:**

- Terminal-styled UI (DaisyUI + Alpine)
- Checks requirements (PHP, SQLite, permissions)
- Creates `.env`, generates `APP_KEY`
- Runs migrations
- Creates admin user
- Activates Terminal theme
- Creates `storage/installed.lock`

**CLI installer:**

```bash
php artisan blogwriter:install
```

Uses Laravel Prompts for interactive setup in terminal.

**Lock file:**
After installation, `storage/installed.lock` prevents re-running installer. Delete to reinstall (dev only).

---

## Testing Strategy

**Pest 4** for all tests.

**Coverage:**

- **Feature tests** — Most tests. HTTP requests, database interactions, authentication
- **Unit tests** — Helpers, transformers, complex logic

**Factories:**
Every model has a factory with realistic fake data.

**Example:**

```php
test('published articles appear in feed')
    ->expect(Article::factory()->published()->create())
    ->and(get('/feed')->json('items.0.title'))
    ->toBe($article->title);
```

**Running tests:**

```bash
php artisan test
php artisan test --filter=ArticleTest
php artisan test --coverage
```

---

## Performance Considerations

**Caching:**

- Feeds cached 5 minutes
- Search results cached 5 minutes per query
- Config cached in production

**Database:**

- SQLite is fast enough for personal blogs
- Indexes on `published_at`, `slug`, `status`
- FTS5 for search (very fast)

**Assets:**

- DaisyUI loaded via CDN (no build step for themes)
- Admin uses Vite for bundling
- Images served from `storage` with HTTP caching headers

**No N+1 queries:**

- Eager load relationships in controllers
- Test suite includes query count assertions

---

## Security

**Auth:**

- Fortify handles authentication
- Rate limiting on login attempts
- CSRF protection on all forms
- Session-based auth (no API tokens for admin)

**File uploads:**

- Validate MIME types
- Sanitize filenames
- Store outside webroot, serve via controller
- EXIF data stripped on display (kept in meta for theme use)

**XSS prevention:**

- User content always escaped (`{{ }}` not `{!! !!}`)
- Editor.js output sanitized before rendering
- CSP headers in production

**SQL injection:**

- Eloquent ORM prevents most issues
- FTS queries use parameter binding

---

## Roadmap & Non-Goals

**Planned:**

- Webmentions (send/receive)
- Micropub support
- ActivityPub integration (optional)
- Theme marketplace

**Planned (future beta releases):**

- Newsletter support (built-in email sending)
- Video integration (Bunny.net or Vimeo API) with optional gating
- Podcast hosting (files + RSS podcast feed)
- Member-only content (paywalled posts)

**Non-Goals (will never support):**

- Multi-author / multi-user
- Comments (use webmentions instead)
- Built-in analytics (use server logs or external tools)
- Third-party ads
- MySQL / Postgres support

BlogWriter is for personal blogs. If you need a full-featured CMS, WordPress, Statamic, Craft CMS, or a headless CMS +
Astro might be better fits. We're focused on indie creators who want ownership and simplicity.

---

## Contributing

**Code style:** Laravel Pint (PSR-12)

**Before submitting PRs:**

1. Run tests: `php artisan test`
2. Run Pint: `vendor/bin/pint`
3. Check no N+1 queries
4. Update docs if changing public API

**Philosophy:**

- Simple over clever
- Explicit over magical
- Standards over custom protocols
- Less code over more features

If a feature makes BlogWriter more complex, it probably doesn't belong.
