---
extends: _layouts.documentation
section: content
category: advanced
order: 99
title: Roadmap
---

# Roadmap

BlogWriter is in pre-alpha. This roadmap shows what's built and what's planned.

## Completed (Pre-Alpha)

### Content

- Article CRUD with EasyMDE Markdown editor
- Statamic-inspired live preview customizer with resizable split-pane editor
- AJAX auto-save while editing
- Draft/published workflow with timestamps
- Featured images (upload or external URL)
- Permalink slug redirects (301) via `past_slugs` column
- `NoH1Heading` content validation
- Photo CRUD with Spatie MediaLibrary integration
- Automatic image conversions (thumbnail, medium, large)
- EXIF metadata extraction and display
- Category management with article associations
- Content newline normalization (preserves code blocks)

### Admin Panel

- Dashboard with article and photo stats
- Sortable, filterable articles table with column toggles
- Per-page pagination dropdown
- Photo management interface
- Category management interface

### Appearance

- 35 DaisyUI themes (21 light, 14 dark) with hover-to-preview
- 11 fonts in 4 categories
- Three-way theme cycling (light, dark, system)
- `config/appearance.php` for available options

### IndieWeb

- Microformats2 markup: h-card (footer, profile page), h-entry (articles, photos), h-feed (home, articles index, photos index, category pages)
- `rel="me"` links (GitHub, Mastodon, Bluesky, Email)

### Infrastructure

- SQLite database
- CLI installer (`php artisan blogwriter:install`) with interactive prompts
- Non-interactive installer mode for automation
- Laravel Fortify authentication with custom Alpine AJAX UI
- Single-user enforcement (`SingleUserViolationException`)
- Progressive Web App support
- Draft media served via authenticated controller (private disk); published media served from public disk
- Profile page with h-card

### Feeds & Distribution

- RSS 2.0, Atom 1.0, and JSON Feed 1.1 at `/feed`, `/atom`, and `/feed.json`
- All feeds include full content for the 20 most recent published items (articles + photos)

### Import & Export

- Markdown export ZIP — download all articles as `.md` files with YAML frontmatter via the admin panel
- ZIP import — restore articles with categories, slugs, and timestamps preserved via the admin panel

### Developer Experience

- Pest 4 test suite with factories
- Laravel Pint code formatting
- Artisan commands: install, create-user, seed, diagnose, profile, uninstall

---

## Planned

### Installation

- Shared hosting installers (Softaculous, Installatron, shell script for SSH)

### Content Types

- Notes (short posts without titles)
- Tags (polymorphic tagging across content types)

### Feeds & Distribution

- Feed discovery `<link>` tags (auto-discovery for feed readers)
- Markdown export (`.md` URLs per article)

### IndieWeb Protocols

- IndieAuth server (authorization, token, metadata endpoints)
- Webmention support (send and receive)

### Admin Improvements

- Tag management interface

---

## Future

### V0.2 — Federation & Discovery

- Micropub support (publish from third-party clients)
- ActivityPub integration
- Webmention dashboard
- POSSE to Bluesky and Mastodon

### V0.3 — Creator Monetization

- Member-only content
- Newsletter support (BYOK email relay)
- Stripe integration

### Beyond V0.3

- Podcast hosting (audio files + RSS podcast feed)
- Video integration (Bunny.net or Vimeo API)
- Theme system with custom templates and overrides
- Plugin architecture (Composer-based, open store spec)
- Theme and plugin marketplace
- Import tools (Substack, Medium, WordPress, Ghost)

### BlogWriter Hosted

A separate management application that provisions BlogWriter instances inside Docker containers. The management app makes artisan calls under the hood — the FOSS product requires CLI skills; the hosted version provides the GUI for non-technical users.

---

## Non-Goals

- Multi-author / multi-user support
- Built-in comments (webmentions planned instead)
- Built-in analytics
- Third-party ads
- MySQL / Postgres support

---

## How to Contribute

[Open an issue on GitHub](https://github.com/jacobodonnell/blogwriter/issues) to request features, suggest priorities, discuss implementation, or report bugs.

Check issues tagged `good first issue` or `help wanted` to get started.

---

## Timeline

No fixed release dates. Features ship when they're ready and tested.

Last updated: February 2026
