# Architecture

Technical overview of BlogWriter for contributors.

---

## Tech Stack

| Layer          | Technology               | Version |
|----------------|--------------------------|---------|
| Backend        | Laravel                  | 12      |
| Language       | PHP                      | 8.4+    |
| Database       | SQLite                   | ---     |
| Frontend       | Alpine.js + Alpine AJAX  | v3      |
| CSS            | Tailwind CSS             | v4      |
| CSS Components | DaisyUI                  | v5      |
| Auth Backend   | Laravel Fortify          | v1      |
| Media          | Spatie MediaLibrary      | ---     |
| Editor         | Tiptap + tiptap-markdown | ---     |
| Testing        | Pest                     | v4      |

---

## Key Architectural Decisions

### SQLite Only

BlogWriter uses SQLite as its only supported database. No MySQL, no Postgres.

- **Simplicity** --- Database is a single file. No separate server process.
- **Portability** --- Copy the file, you've copied the database.
- **Hosting** --- Works on cheap shared hosting without database server access.
- **Performance** --- More than fast enough for a single-author blog.

### Alpine.js Everywhere (No Livewire)

The entire application --- admin and public --- uses Alpine.js for interactivity. No Livewire, no Vue, no React.

Alpine AJAX handles dynamic updates (auto-save, search, form submissions, sortable tables) without page reloads.
Standard Laravel controllers handle the backend.

### Custom Auth UI

Authentication uses Laravel Fortify for the backend, with a custom login UI built using Alpine AJAX and DaisyUI forms.
Login is the only web auth flow — registration, password reset, and email verification are all handled via CLI commands.

### Single-Author Only

One admin account. One blog. Registration is disabled after the first user is created. A `SingleUserViolationException`
is thrown if additional user creation is attempted. Enforcement is via a `booted()` model event on `User` that runs
before any save.

### Tiptap Editor

Articles use Tiptap, a WYSIWYG editor with a rich formatting toolbar and four layout modes (fullscreen, split, classic,
preview). Content is entered as WYSIWYG and stored as Markdown in the database via the tiptap-markdown extension — users
never see raw Markdown syntax. A markdown mode toggle switches between WYSIWYG and raw markdown editing.

The toolbar provides: Bold, Italic, H2–H5, Blockquote, Bullet list, Ordered list, Link, Image, Inline code, Horizontal
rule, YouTube embed (dialog), Undo, Redo, and Markdown toggle.

A revision history system tracks changes on every save. Revisions use a hybrid snapshot + unified diff storage strategy
(see ArticleRevision model below). The revision browser panel lets users preview, restore, and delete past revisions.

Custom extensions:

- **Resizable figure** (`resizable-figure.js`) — Images wrapped in `<figure>` with drag handles for resizing, caption
  support, and full-width toggle. Stored as `![alt|width:50%|caption:\`text\`](url)` internally.
- **YouTube embeds** — Rendered as responsive iframes; stored as `@[youtube](url)` internally

### Content Newline Normalization

Article content undergoes newline normalization: double newlines are collapsed on save and expanded on read, while
preserving formatting inside code blocks. This ensures consistent storage and display.

### Dual-Disk Media Storage

Photos use conditional disk assignment based on publish status. Draft photos are stored on the `private` disk and served
through `MediaController` with authentication checks. Published photos are moved to the `public` disk and served
directly via symlink — no controller overhead, no auth required.

---

## Directory Structure

```
blogwriter/
├── app/
│   ├── Actions/
│   │   ├── Photos/
│   │   │   ├── CreatePhotoFromUploadAction.php
│   │   │   ├── ExtractExifDataAction.php
│   │   │   └── HandleArticlePhotoUploadAction.php
│   │   ├── ApplyArticleFeaturedImageAction.php
│   │   ├── CreateCategoryFromArticleAction.php
│   │   ├── GenerateUniqueSlugAction.php
│   │   └── NormalizeCaptionMetaAction.php
│   ├── Console/
│   │   └── Commands/
│   │       ├── Concerns/
│   │       │   ├── PromptsForPassword.php
│   │       │   └── ValidatesInput.php
│   │       ├── CreateUserCommand.php
│   │       ├── DiagnoseCommand.php
│   │       ├── InstallCommand.php          # php artisan blogwriter:install
│   │       ├── ProfileCommand.php
│   │       ├── ResetPasswordCommand.php
│   │       ├── SeedCommand.php
│   │       └── UninstallCommand.php        # php artisan blogwriter:uninstall
│   ├── DTOs/
│   │   └── FeedItem.php                    # Typed DTO for feed entries
│   ├── Enums/
│   │   └── Status.php                      # Draft / Published
│   ├── Exceptions/
│   │   ├── PhotoUploadFailedException.php
│   │   └── SingleUserViolationException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminPhotoController.php
│   │   │   │   ├── AppearanceController.php
│   │   │   │   ├── ArticleController.php   # Article CRUD
│   │   │   │   ├── ArticleDownloadController.php
│   │   │   │   ├── RevisionController.php  # Show/delete article revisions
│   │   │   │   ├── ArticleExportController.php
│   │   │   │   ├── ArticleImportController.php
│   │   │   │   ├── ArticleLivePreviewController.php
│   │   │   │   ├── ArticlePreviewController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── CategoryExploreController.php
│   │   │   │   ├── CreateArticleController.php
│   │   │   │   ├── CreateArticlePreviewController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ExportController.php
│   │   │   │   ├── MediaController.php     # Serves draft media from private disk
│   │   │   │   ├── PhotoDownloadController.php
│   │   │   │   ├── PlaceholderImageController.php
│   │   │   │   ├── ProfileSettingsController.php
│   │   │   │   ├── RobotsSettingsController.php
│   │   │   │   └── SiteSettingsController.php
│   │   │   ├── AboutController.php         # Public about page
│   │   │   ├── ArticleController.php       # Public article display
│   │   │   ├── FeedController.php          # RSS, Atom, JSON Feed
│   │   │   ├── HomeController.php          # Public homepage
│   │   │   ├── InstallController.php       # Install page (shows CLI instructions)
│   │   │   ├── PhotoController.php         # Public photo display
│   │   │   └── RobotsController.php        # Dynamic robots.txt
│   │   └── Requests/
│   │       ├── Admin/
│   │       │   ├── StorePhotoRequest.php
│   │       │   ├── UpdateAppearanceRequest.php
│   │       │   ├── UpdatePageSettingsRequest.php
│   │       │   ├── UpdatePhotoRequest.php
│   │       │   ├── UpdatePlaceholderImageRequest.php
│   │       │   ├── UpdateProfileRequest.php
│   │       │   └── UpdateRobotsRequest.php
│   │       ├── Concerns/
│   │       │   ├── ArticleRules.php
│   │       │   └── ValidatesFeaturedImage.php
│   │       ├── ArticleImportRequest.php
│   │       ├── CategoryRequest.php
│   │       ├── StoreArticleRequest.php
│   │       ├── UpdateArticlePreviewRequest.php
│   │       └── UpdateArticleRequest.php
│   ├── Models/
│   │   ├── Concerns/
│   │   │   └── InvalidatesResponseCache.php
│   │   ├── Article.php
│   │   ├── ArticleRevision.php
│   │   ├── Category.php
│   │   ├── Photo.php
│   │   ├── Setting.php
│   │   └── User.php
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── FortifyServiceProvider.php
│   ├── Services/
│   │   ├── ArticleExportService.php
│   │   ├── ArticleImportService.php
│   │   ├── ContentFilterService.php
│   │   ├── FeedService.php                 # Merges articles + photos for feeds
│   │   ├── InstallService.php
│   │   ├── PasswordGenerator.php
│   │   ├── PasswordRules.php
│   │   ├── PhotoExportService.php
│   │   ├── ResetService.php
│   │   └── RevisionService.php         # Diff generation, reconstruction, deletion
│   ├── Support/
│   │   ├── ImportResult.php
│   │   ├── Markdown.php                    # Custom Markdown renderer
│   │   ├── ParsedImport.php
│   │   ├── PreflightResult.php
│   │   └── ResponseCacheProfile.php
│   └── View/
│       └── Components/
│           ├── Articles/
│           │   └── Customizer.php
│           ├── FilterBanner/
│           │   ├── CategorySelect.php
│           │   ├── FilterBanner.php
│           │   ├── FilterField.php
│           │   ├── PerPage.php
│           │   ├── Search.php
│           │   ├── Select.php
│           │   └── Sort.php
│           ├── Layouts/
│           │   └── Base.php
│           ├── ArticleCard.php
│           ├── FlashMessages.php
│           └── PhotoCard.php
├── config/
│   └── appearance.php                      # Themes, fonts, defaults
├── database/
│   ├── database.sqlite                     # Created by installer
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── public/                         # Public-facing pages
│   │   │   ├── index.blade.php             # Homepage (h-feed)
│   │   │   ├── articles.blade.php          # Articles index (h-feed)
│   │   │   ├── article.blade.php           # Single article (h-entry)
│   │   │   ├── category.blade.php          # Category page (h-feed)
│   │   │   └── about.blade.php             # Profile/about page (h-card)
│   │   ├── photos/
│   │   │   ├── index.blade.php             # Photos index (h-feed)
│   │   │   └── show.blade.php              # Single photo (h-entry)
│   │   ├── feeds/
│   │   │   ├── rss.blade.php               # RSS 2.0 feed
│   │   │   └── atom.blade.php              # Atom 1.0 feed
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── articles/
│   │   │   ├── photos/
│   │   │   ├── categories/
│   │   │   └── settings/
│   │   ├── auth/                           # Custom Fortify auth UI
│   │   │   └── login.blade.php
│   │   ├── components/                     # Shared Blade components
│   │   │   ├── layouts/                    # Layout components
│   │   │   ├── article-save-button.blade.php
│   │   │   ├── copy-link-button.blade.php
│   │   │   ├── editor-modal.blade.php
│   │   │   ├── photo-exif-details.blade.php
│   │   │   └── share-buttons.blade.php
│   │   └── install/                        # Web installer views
│   ├── css/
│   │   └── app.css                         # Tailwind entry point
│   └── js/
│       ├── app.js                          # Alpine.js setup
│       ├── bootstrap.js
│       ├── components/
│       │   ├── article-customizer/
│       │   │   ├── index.js                # Editor core, keyboard shortcuts
│       │   │   ├── dirty-tracker.js        # Unsaved changes detection
│       │   │   └── revision-browser.js     # Revision preview/restore/delete
│       │   └── customizer-layout.js        # Editor mode switching
│       └── extensions/
│           └── resizable-figure.js         # Draggable figure with captions
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Models

### Article

- `title`, `slug`, `past_slugs` (JSON), `summary`, `content` (Markdown), `status`, `published_at`, `last_edited_at`,
  `meta` (JSON)
- `belongsTo(User)`, `belongsTo(Category)`, `belongsTo(Photo, 'photo_id')` for featured image
- `content_html` accessor renders Markdown to HTML
- `past_slugs` enables 301 redirects when slugs change

### ArticleRevision

- `title`, `content` (full snapshot or unified diff), `created_at`
- `belongsTo(Article)` — cascading delete when the parent article is removed
- Indexed on `(article_id, id)` for efficient chain replay
- `RevisionService` handles diff generation (`SebastianBergmann\Diff`), content reconstruction by replaying the diff
  chain, and safe deletion with chain recalculation
- First revision stores a full content snapshot; subsequent revisions store unified diffs against the previous state

### Photo

- `filename`, `slug`, `past_slugs` (JSON), `caption` (Markdown), `alt_text`, `status`, `published_at`, `taken_at`,
  `meta` (JSON for EXIF)
- `belongsTo(User)`, `belongsTo(Category)`
- Uses Spatie MediaLibrary: `HasMedia` interface, `InteractsWithMedia` trait
- Conversions: thumbnail (300×300), medium (768×768), large (1536×1536)
- `past_slugs` enables 301 redirects when slugs change (same behavior as articles)

### Category

- `name`, `slug`, `description`, `parent_id`
- `belongsTo(Category)` (parent), `hasMany(Category)` (children) — hierarchical parent/child subcategories
- `hasMany(Article)`, `hasMany(Photo)`
- Slug auto-generated from name
- Recursive CTEs for tree operations: `flatTree()` (ordered tree for dropdowns), `ancestors()` (breadcrumb path),
  `descendantIds()` (all nested children)

### User

- Standard Laravel user with Fortify two-factor columns
- Single-user enforcement at the registration action level

### Setting

- Key-value store for application settings (appearance preferences, etc.)

---

## Content Storage

| Type    | Editor  | Storage              | Rendering         |
|---------|---------|----------------------|-------------------|
| Article | Tiptap  | `content` (Markdown) | Markdown -> HTML  |
| Photo   | Upload  | MediaLibrary files   | Image conversions |
| Photo   | Caption | `caption` (Markdown) | Markdown -> HTML  |

---

## Authentication Flow

### Backend: Laravel Fortify

Fortify provides routes and controllers. Only `/login` and `/logout` are active web routes:

- `/login` --- POST to authenticate
- `/logout` --- POST to destroy session

Registration, password reset, and email verification are disabled. User creation and password resets are handled via CLI
commands (`blogwriter:install`, `blogwriter:reset-password`).

BlogWriter does not send or receive email out of the box.

### Frontend: Custom Alpine UI

All auth views use Alpine AJAX (`x-target`) for form submissions. On success, redirect. On error, validation errors
display inline.

---

## Appearance System

BlogWriter's appearance is controlled through the `Setting` model and `config/appearance.php`:

- 35 DaisyUI themes (21 light, 14 dark) stored in config
- 11 fonts in 4 categories stored in config
- User selections saved in the `settings` table
- Three-way theme cycling: light, dark, system preference
- `AppearanceController` handles the settings UI

---

## Testing

**Pest 4** for all tests.

- **Feature tests** --- HTTP requests, database interactions, authentication
- **Unit tests** --- Helpers, transformers, complex logic
- **Factories** --- Every model has a factory with realistic fake data

```bash
php artisan test                        # Run all tests
php artisan test --filter=ArticleTest   # Filter by name
php artisan test --compact              # Compact output
```

---

## Security

- Fortify handles authentication with rate limiting on login attempts
- CSRF protection on all forms
- Session-based auth (no API tokens)
- File uploads validated by MIME type, filenames sanitized
- Draft media protected on private disk, served via authenticated controller; published media publicly accessible
- User content escaped with `{{ }}` (not `{!! !!}` except for rendered HTML content)
- Single-user enforcement prevents unauthorized account creation

---

## Non-Goals

- Multi-author / multi-user support
- Built-in comments (webmentions are planned instead)
- Built-in analytics
- Third-party ads
- MySQL / Postgres support

#### [Up Next: *Roadmap*](roadmap.md)
