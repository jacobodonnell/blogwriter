# BlogWriter

**Own your content. Own your domain.**

BlogWriter is a personal blogging platform you install on your own server. No platform fees, no content policies you didn't write, no risk of your work disappearing because a company pivoted. Your words, your photos, your domain.

## Features

**Content:**

- **Articles** --- Long-form posts with EasyMDE Markdown editor, live preview customizer, categories, featured images, and permalink slug redirects
- **Photos** --- Image posts with captions, alt text, EXIF display, and automatic image conversions (thumbnail, medium, large) via Spatie MediaLibrary

**Appearance:**

- 35 DaisyUI themes (21 light, 14 dark) with hover-to-preview
- 11 fonts across 4 categories
- Three-way theme cycling: light, dark, system preference

**Admin:**

- Dashboard with content stats
- Sortable, filterable articles table with column toggles and per-page pagination
- WordPress-style customizer with resizable split-pane preview and AJAX auto-save

**IndieWeb:**

- Microformats2 markup: h-card, h-entry, h-feed
- `rel="me"` links for identity verification (GitHub, Mastodon, Bluesky, Email)
- RSS/Atom/JSON feeds and IndieAuth are planned

**Infrastructure:**

- SQLite database --- no MySQL server to manage
- Single admin user --- personal blogs, not a multi-tenant platform
- One Artisan command to install
- Runs on any PHP 8.4+ host
- Progressive Web App support

## Quick Start

```bash
# Clone the repository
git clone https://github.com/jacobodonnell/blogwriter.git
cd blogwriter

# Install dependencies
composer install
npm install && npm run build

# Run the installer
php artisan blogwriter:install
```

The installer checks requirements, creates your database, sets up your admin account, and configures your site.

**Requirements:** PHP 8.4+. No separate database server. No Redis. No Node.js in production.

## Tech Stack

| Layer | Technology | Why |
|-------|-----------|-----|
| Backend | Laravel 12 | Modern PHP framework |
| Database | SQLite | Single file, zero config, runs anywhere |
| Frontend | Alpine.js + Alpine AJAX | Reactive without the complexity |
| CSS | Tailwind CSS v4 + DaisyUI | Utility-first with pre-built components |
| Auth | Laravel Fortify | Headless auth, custom UI |
| Media | Spatie MediaLibrary | Image handling and conversions |
| Editor | EasyMDE | Markdown editing with toolbar and preview |
| Testing | Pest 4 | Expressive, readable tests |

## Documentation

Full documentation is in the [`docs/`](docs/) directory:

- [Introduction](docs/introduction.md) --- Overview
- [Installation](docs/installation.md) --- Get BlogWriter running
- [Writing Content](docs/writing-content.md) --- Articles and photos
- [Appearance](docs/appearance.md) --- Themes and fonts
- [Settings](docs/settings.md) --- Configuration
- [Feeds & IndieWeb](docs/feeds-and-indieweb.md) --- Microformats, feeds, IndieWeb
- [Architecture](docs/architecture.md) --- Technical overview
- [Roadmap](docs/roadmap.md) --- What's built, what's next

## Roadmap

BlogWriter is in pre-alpha.

**Built:** Articles, Photos, Categories, Appearance (35 themes, 11 fonts), Admin panel, CLI Installer, Auth, MediaLibrary, Microformats (h-card, h-entry, h-feed, rel="me"), PWA

**Planned:** Notes, Tags, RSS/Atom/JSON feeds, IndieAuth, Webmentions

**Future:** Monetization, podcasts, video, plugin marketplace, managed hosting

See the full [Roadmap](docs/roadmap.md).

## Contributing

Contributions are welcome. Before submitting a PR:

1. Run tests: `php artisan test`
2. Run formatter: `vendor/bin/pint`
3. Read the [Architecture](docs/architecture.md) doc

**Philosophy:** Simple over clever. Explicit over magical. Standards over custom protocols. Less code over more features.

## Standing on the Shoulders of Giants

BlogWriter exists because of the open-source community:

- **[Laravel](https://laravel.com)** --- Taylor Otwell and the Laravel team
- **[Alpine.js](https://alpinejs.dev)** --- Caleb Porzio
- **[Alpine AJAX](https://alpine-ajax.js.org)** --- Christian Taylor
- **[Tailwind CSS](https://tailwindcss.com)** --- Adam Wathan and Tailwind Labs
- **[Pest](https://pestphp.com)** --- Nuno Maduro
- **[Spatie](https://spatie.be)** --- The Spatie team for MediaLibrary and many other packages
- **[DaisyUI](https://daisyui.com)** --- Pouya Saadeghi

## License

BlogWriter is open-source software licensed under the [MIT License](LICENSE).
