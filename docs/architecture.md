---
title: Architecture
description: Technical overview of BlogWriter's codebase, stack, and design decisions.
extends: _layouts.documentation
section: content
category: advanced
order: 8
---

# Architecture

Technical overview of BlogWriter for contributors.

---

## Tech Stack

| Layer             | Technology              | Version |
|-------------------|-------------------------|---------|
| Backend           | Laravel                 | 12      |
| Language          | PHP                     | 8.4+    |
| Database          | SQLite                  | ---     |
| Frontend          | Alpine.js + Alpine AJAX | v3      |
| CSS               | Tailwind CSS            | v4      |
| CSS Components    | DaisyUI                 | v5      |
| Auth Backend      | Laravel Fortify         | v1      |
| Media             | Spatie MediaLibrary     | ---     |
| Editor            | EasyMDE                 | ---     |
| Testing           | Pest                    | v4      |

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

Alpine AJAX handles dynamic updates (auto-save, search, form submissions, sortable tables) without page reloads. Standard Laravel controllers handle the backend.

### Custom Auth UI

Authentication uses Laravel Fortify for the backend, with a custom login UI built using Alpine AJAX and DaisyUI forms. Login is the only web auth flow — registration, password reset, and email verification are all handled via CLI commands.

### Single-Author Only

One admin account. One blog. Registration is disabled after the first user is created. A `SingleUserViolationException` is thrown if additional user creation is attempted through Fortify's registration action.

### EasyMDE Editor

Articles use EasyMDE, a Markdown editor with toolbar buttons and preview. Content is stored as Markdown in the database.

### Content Newline Normalization

Article content undergoes newline normalization: double newlines are collapsed on save and expanded on read, while preserving formatting inside code blocks. This ensures consistent storage and display.

### Dual-Disk Media Storage

Photos use conditional disk assignment based on publish status. Draft photos are stored on the `private` disk and served through `MediaController` with authentication checks. Published photos are moved to the `public` disk and served directly via symlink — no controller overhead, no auth required.

---

## Directory Structure

```
blogwriter/
├── app/
│   ├── Actions/
│   │   ├── Fortify/                        # Fortify auth actions
│   │   ├── Photos/                         # Photo-related actions
│   │   ├── GenerateArticleSummaryAction.php
│   │   ├── GenerateUniqueSlugAction.php
│   │   └── UpdatePublishedStatusAction.php
│   ├── Console/
│   │   └── Commands/
│   │       ├── InstallCommand.php          # php artisan blogwriter:install
│   │       ├── CreateUserCommand.php
│   │       ├── SeedCommand.php
│   │       ├── BundleCommand.php
│   │       ├── CheckImageHealth.php
│   │       ├── DiagnoseCommand.php
│   │       └── ProfileCommand.php
│   ├── Exceptions/
│   │   └── SingleUserViolationException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ArticleController.php       # Public article display
│   │   │   ├── PhotoController.php         # Public photo display
│   │   │   ├── CategoryArticleController.php
│   │   │   ├── HomeController.php          # Public homepage
│   │   │   ├── InstallController.php       # Install page (shows CLI instructions)
│   │   │   └── Admin/
│   │   │       ├── ArticleController.php   # Article CRUD
│   │   │       ├── CreateArticleController.php
│   │   │       ├── ArticlePreviewController.php
│   │   │       ├── CreateArticlePreviewController.php
│   │   │       ├── AdminPhotoController.php
│   │   │       ├── CategoryController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── SettingsController.php
│   │   │       ├── AppearanceController.php
│   │   │       └── MediaController.php     # Serves draft media from private disk
│   │   └── Requests/
│   │       ├── StoreArticleRequest.php
│   │       ├── UpdateArticleRequest.php
│   │       ├── UpdateArticlePreviewRequest.php
│   │       ├── StoreCategoryRequest.php
│   │       └── Admin/                      # Admin-specific requests
│   ├── Models/
│   │   ├── Article.php
│   │   ├── Photo.php
│   │   ├── Category.php
│   │   ├── User.php
│   │   ├── Setting.php
│   │   └── SystemEvent.php
│   └── View/
│       └── Components/
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
│   │   │   └── profile.blade.php           # Profile page (h-card)
│   │   ├── photos/
│   │   │   ├── index.blade.php             # Photos index (h-feed)
│   │   │   └── show.blade.php              # Single photo (h-entry)
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
│       └── bootstrap.js
└── tests/
    ├── Feature/
    └── Unit/
```

---

## Models

### Article

- `title`, `slug`, `past_slugs` (JSON), `summary`, `content` (Markdown), `status`, `published_at`, `last_edited_at`, `meta` (JSON)
- `belongsTo(User)`, `belongsToMany(Category)`, `belongsTo(Photo, 'photo_id')` for featured image
- `content_html` accessor renders Markdown to HTML
- `past_slugs` enables 301 redirects when slugs change

### Photo

- `filename`, `slug`, `caption` (Markdown), `alt_text`, `status`, `published_at`, `taken_at`, `meta` (JSON for EXIF)
- `belongsTo(User)`
- Uses Spatie MediaLibrary: `HasMedia` interface, `InteractsWithMedia` trait
- Conversions: thumbnail (150x150), medium (800x600), large (1600x1200)

### Category

- `name`, `slug`, `description`
- `belongsToMany(Article)`
- Slug auto-generated from name

### User

- Standard Laravel user with Fortify two-factor columns
- Single-user enforcement at the registration action level

### Setting

- Key-value store for application settings (appearance preferences, etc.)

### SystemEvent

- Tracks system-level events (installation, etc.)

---

## Content Storage

| Type    | Editor  | Storage             | Rendering        |
|---------|---------|---------------------|------------------|
| Article | EasyMDE | `content` (Markdown)| Markdown -> HTML |
| Photo   | Upload  | MediaLibrary files  | Image conversions|
| Photo   | Caption | `caption` (Markdown)| Markdown -> HTML |

---

## Authentication Flow

### Backend: Laravel Fortify

Fortify provides routes and controllers. Only `/login` and `/logout` are active web routes:

- `/login` --- POST to authenticate
- `/logout` --- POST to destroy session

Registration, password reset, and email verification are disabled. User creation and password resets are handled via CLI commands (`blogwriter:install`, `blogwriter:user:reset-password`).

BlogWriter does not send or receive email out of the box.

### Frontend: Custom Alpine UI

All auth views use Alpine AJAX (`x-target`) for form submissions. On success, redirect. On error, validation errors display inline.

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

#### [Up Next: *Roadmap*](/docs/advanced/roadmap)
