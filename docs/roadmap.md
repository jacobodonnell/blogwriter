---
extends: _layouts.documentation
section: content
category: advanced
order: 99
title: Roadmap
---

# Roadmap

BlogWriter is in active development. This roadmap shows what's implemented, what's in progress, and what's planned for future releases.

## V0.1 - MVP for Personal Use

**Goal:** A working personal blog platform with core features for publishing articles and photos.

### ✅ Completed

**Content Management:**
- Article CRUD (create, read, update, delete)
- Markdown editor for articles
- Photo CRUD with Spatie MediaLibrary integration
- Category management for articles
- Draft/publish workflow for articles and photos
- Featured photo support for articles

**Admin Panel:**
- Dashboard with content overview
- Article management interface
- Photo management interface
- Category management interface
- Authentication (Laravel Fortify with custom UI)
- Email verification support (2FA columns ready)

**Infrastructure:**
- SQLite database
- CLI installer with interactive prompts (`php artisan blogwriter:install`)
- Non-interactive installer mode for automation
- Laravel 12, PHP 8.4+, Alpine.js v3, Tailwind CSS v4
- Spatie MediaLibrary for professional photo handling
- Multiple image conversions (thumbnail, medium, large)

**Database Schema:**
- Articles table with Markdown content
- Photos table with MediaLibrary integration
- Categories table with article relationships
- Settings table (structure ready)
- User authentication tables

### 🚧 In Progress

No active development tasks at this time. Next: prioritize features from "Planned for V0.1" below.

### 📋 Planned for V0.1

**Content Types:**
- Note model, controller, views, and routes
- Polymorphic Tag model and relationships (articles, notes, photos)

**Publishing Features:**
- RSS/Atom/JSON feed generation
- Microformats markup (h-card, h-entry, h-feed)
- Auto-save for article editor
- Markdown export (`.md` URLs)
- Automatic Markdown file backups

**IndieWeb:**
- IndieAuth endpoints (authorization, token, metadata)
- Webmention support (send/receive)

**Admin Improvements:**
- Settings UI (currently minimal/read-only)
- Profile editing interface
- Theme selection interface (when theme system is built)
- Tag management interface

**User Experience:**
- Web installer UI (terminal-styled wizard)
- Editor.js block editor (alternative to Markdown textarea)
- Search functionality (SQLite FTS5)

### 🎨 Theme System (Major Feature)

**Not yet started. Requires significant architecture work:**

- `themes/` directory structure
- Laravel Folio-based routing
- Terminal theme (default, retro aesthetic)
- Starter theme (minimalist foundation)
- Component override system
- Theme upload/activation UI
- `php artisan theme:install` command

**Why delayed:** Core content features (Articles, Notes, Photos, Tags, Feeds) are higher priority. Theme system can be built once content is solid.

---

## V0.2 - Enhanced IndieWeb Integration

**Prerequisites:** V0.1 complete with all core features working.

### Planned Features

**IndieWeb:**
- Micropub support (publish from third-party clients)
- ActivityPub integration (optional, federated social)
- Enhanced microformats (h-review, h-recipe)
- Webmention dashboard

**Content:**
- Multi-photo posts (galleries)
- Video integration (Bunny.net or Vimeo API, optional gating)
- Podcast hosting (audio files + RSS podcast feed)

**Platform:**
- Theme marketplace (browse, install, purchase themes)
- Plugin architecture (foundation)

---

## V0.3 - Monetization & Community

**Prerequisites:** V0.2 complete, stable user base.

### Planned Features

**Membership:**
- Member-only content (paywalled posts)
- Newsletter support (built-in email sending)
- Stripe integration for payments

**Community:**
- Comments system (optional, using Webmentions)
- Reader interactions dashboard

**Performance:**
- Full-text search improvements
- Caching optimizations
- Asset optimization

---

## Implementation Status by Feature Category

### Content Types
- ✅ **Articles** - Full CRUD, Markdown editor, categories, featured photos
- 🚧 **Notes** - Planned (model, controller, views needed)
- ✅ **Photos** - Full CRUD with MediaLibrary, conversions, captions

### Admin Features
- ✅ **Dashboard** - Content overview, quick actions
- ✅ **Authentication** - Fortify custom UI, email verification ready
- ⚠️ **Settings** - Minimal UI (full settings UI planned)
- 🚧 **Tags** - Interface planned (after Tag model built)

### Publishing & Distribution
- 🚧 **Feeds** - RSS/Atom/JSON planned
- 🚧 **Microformats** - h-card/h-entry/h-feed planned
- 🚧 **IndieAuth** - Endpoints planned
- 🚧 **Webmentions** - Send/receive planned

### Developer Experience
- ✅ **CLI Installer** - Fully working, interactive & non-interactive modes
- 🚧 **Web Installer** - UI planned (CLI works now)
- 🚧 **Theme System** - Complete system planned
- ✅ **Database** - SQLite, migrations, seeders ready
- ✅ **Testing** - Pest 4, factories, comprehensive test suite

---

## How to Contribute

**Feedback welcome!** [Open an issue on GitHub](https://github.com/jacobodonnell/blogwriter/issues) to:

- Request features
- Suggest priorities
- Discuss implementation approaches
- Report bugs in existing features

**Want to help build?** Check GitHub issues tagged `good first issue` or `help wanted`.

---

## Non-Goals

**These features are intentionally excluded from BlogWriter's scope:**

- ❌ Multi-author / multi-user support
- ❌ Built-in comments (use Webmentions instead)
- ❌ Built-in analytics (use server logs or external tools)
- ❌ Third-party ads integration
- ❌ MySQL / Postgres support (SQLite only by design)
- ❌ Multi-tenancy (this is for personal blogs)

BlogWriter is for personal blogs. If you need a full-featured CMS, consider WordPress, Statamic, Craft CMS, or a headless CMS + static site generator.

---

## Timeline Philosophy

We don't provide fixed release dates. Features are released when they're ready and properly tested. Quality and simplicity over rushing to ship.

**Current Focus:** Completing V0.1 core features (Notes, Tags, Feeds, IndieWeb basics).

Last updated: February 2026
