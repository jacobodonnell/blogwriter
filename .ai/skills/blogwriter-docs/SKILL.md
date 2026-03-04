---
name: blogwriter-docs
description: Reference BlogWriter documentation and specifications when building the platform.
---

# BlogWriter Documentation Reference

Access BlogWriter documentation from the local `docs/` directory to understand specifications and implementation status while actively building the platform.

## Critical Implementation Note

**Some features documented are specifications for future implementation, not current state.** Documentation includes both:
- ✅ **Implemented features** - Articles, Photos, Categories, Admin, Authentication, CLI Installer, Feeds, Microformats, Import/Export, Appearance System, PWA
- 🚧 **Coming Soon (V0.2)** - Subscribers, IndieAuth, Stripe/Cashier, Buttondown Newsletter, Pages, Webmentions, Member-only Content
- 🚧 **Coming Soon (V0.3)** - Notes, Micropub, ActivityPub, POSSE

**Always verify actual codebase implementation status before claiming a feature exists.**

## Documentation Files

All files use YAML frontmatter (HydePHP-compatible format for future static site generation):

### Getting Started
- **`introduction.md`** - Product vision, target audience, content types
- **`installation.md`** - CLI installer (✅ working), Web installer UI (🚧 coming soon)
- **`local-development.md`** - Local development setup (✅)
- **`deployment.md`** - Production deployment with Laravel Forge (✅)
- **`roadmap.md`** - Feature status, milestones, and future plans

### Content & Writing
- **`writing-content.md`** - Tiptap WYSIWYG editor with Markdown storage (✅), Photos (✅), Tags (🚧 coming soon)

### Customization
- **`appearance.md`** - 35 DaisyUI themes, 11 fonts, three-way theme cycling (✅)

### Configuration
- **`settings.md`** - Settings UI (✅ functional)
- **`feeds-and-indieweb.md`** - RSS/Atom/JSON feeds (✅), microformats (✅), IndieAuth (🚧 coming soon), Webmentions (🚧 coming soon)

### Architecture & Technical
- **`architecture.md`** - Tech stack, models (Article ✅, Photo ✅, Note 🚧, Tag 🚧), database structure, design decisions

## Activation Triggers

Activate this skill when:
- User asks about BlogWriter architecture, models, or design specifications
- User asks about themes, appearance, or customization features
- User asks about the installation system or requirements
- User asks about configuration, settings, or IndieWeb integration
- User references "docs", "documentation", or "specifications"
- User mentions building or implementing Articles, Photos, or Pages
- User needs to understand content types or data models
- User asks about feeds, IndieAuth, or IndieWeb features
- User asks about subscribers, newsletter, payments, or content gating

## Using the Documentation

Read files directly from the `docs/` directory using the Read tool:
- Full path: `/Users/jacobodonnell/Dev-Projects/php/laravel/blogwriter/docs/[filename].md`
- Or relative from project root: `docs/[filename].md`

## Implementation Status Key

- ✅ **Implemented** - Feature is built and working
- 🚧 **Coming Soon** - Feature is documented but not yet implemented (specification only)

## Current Implementation vs Documentation

**What's Actually Built (✅):**
- Articles: Full CRUD, Tiptap WYSIWYG editor (Markdown storage), categories, featured photos, draft/publish workflow
- Photos: Full CRUD, Spatie MediaLibrary integration, image conversions, EXIF extraction
- Categories: Full CRUD, article relationships
- Admin Panel: Dashboard, article/photo/category management, sortable/filterable tables
- Authentication: Laravel Fortify with custom Alpine AJAX UI, email verification, 2FA columns
- CLI Installer: Fully working with interactive/non-interactive modes
- Feeds: RSS 2.0, Atom 1.0, JSON Feed 1.1 with auto-discovery
- Microformats2: h-card (footer, profile), h-entry (articles, photos), h-feed (indexes)
- Import/Export: Markdown ZIP export and ZIP import with category/slug preservation
- Appearance: 35 DaisyUI themes, 11 fonts, three-way theme cycling (light/dark/system)
- PWA: Progressive Web App support
- Response Caching: Performance optimization for public pages
- Settings UI: Functional settings management

**What's Coming in V0.2 (🚧):**
- Subscribers: Subscriber model, custom auth guard, IndieAuth login, magic link fallback
- Monetization: Stripe via Laravel Cashier (Checkout + Customer Portal)
- Newsletter: Buttondown integration with newsletter provider interface
- Content Gating: Member-only content (everyone/subscribers/paid visibility)
- IndieAuth: Server endpoints (authorization, token, metadata)
- Pages: Home + about pages with layout choices
- Webmentions: Send and receive
- Authenticated Feeds: Premium content delivery via feed readers

**What's Coming in V0.3 (🚧):**
- Notes: Short posts with hashtag-to-tag parsing
- Micropub: Publish from third-party clients
- ActivityPub: Federation support
- POSSE: Syndication to Bluesky and Mastodon
