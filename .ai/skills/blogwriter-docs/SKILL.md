---
name: blogwriter-docs
description: Reference BlogWriter documentation and specifications when building the platform.
---

# BlogWriter Documentation Reference

Access BlogWriter documentation from the local `docs/` directory to understand specifications and implementation status while actively building the platform.

## Critical Implementation Note

**Many features documented are specifications for future implementation, not current state.** Documentation includes both:
- ✅ **Implemented features** - Articles, Photos, Categories, Admin, Authentication, CLI Installer
- 🚧 **Coming Soon features** - Notes, Tags, Themes, Feeds, IndieWeb, Web Installer UI

**Always verify actual codebase implementation status before claiming a feature exists.**

## Documentation Files

All files use YAML frontmatter (HydePHP-compatible format for future static site generation):

### Getting Started
- **`introduction.md`** - Product vision, target audience, content types (Articles, Photos, Notes*)
- **`installation.md`** - CLI installer (✅ working), Web installer UI (🚧 coming soon)
- **`roadmap.md`** - Feature status, V0.1 progress, and future milestones

### Content & Writing
- **`writing-content.md`** - Markdown editor (✅ current), Editor.js (🚧 coming soon), Notes (🚧 coming soon), Photos, Tags (🚧 coming soon)

### Customization (Planned)
- **`themes.md`** - Theme system specification (🚧 coming soon - entire system not yet built)
- **`components.md`** - Component library (stub, needs content or specification)

### Configuration
- **`settings.md`** - Settings UI (minimal/read-only currently, extensive UI 🚧 coming soon)
- **`feeds-and-indieweb.md`** - RSS/Atom/JSON feeds, microformats, IndieAuth, Webmentions (🚧 all coming soon)

### Architecture & Technical
- **`architecture.md`** - Tech stack, models (Article ✅, Photo ✅, Note 🚧, Tag 🚧), database structure, design decisions

## Activation Triggers

Activate this skill when:
- User asks about BlogWriter architecture, models, or design specifications
- User asks about themes, components, or customization features
- User asks about the installation system or requirements
- User asks about configuration, settings, or IndieWeb integration
- User references "docs", "documentation", or "specifications"
- User mentions building or implementing Articles, Notes, or Photos
- User is working on theme system or component library
- User needs to understand content types or data models
- User asks about feeds, IndieAuth, or IndieWeb features

## Using the Documentation

Read files directly from the `docs/` directory using the Read tool:
- Full path: `/Users/jacobodonnell/Dev-Projects/php/laravel/blogwriter/docs/[filename].md`
- Or relative from project root: `docs/[filename].md`

Example:
```
Read tool: docs/architecture.md
```

## Implementation Status Key

- ✅ **Implemented** - Feature is built and working
- 🚧 **Coming Soon** - Feature is documented but not yet implemented (specification only)
- Documentation with "Coming Soon" callouts includes GitHub issue links for feedback

## Important Reminders

1. **Verify before implementing** - Check actual codebase to confirm if documented feature exists
2. **YAML frontmatter preserved** - Will be used by HydePHP for static site generation
3. **Specifications vs Reality** - Documentation serves dual purpose: current features + planned roadmap
4. **GitHub feedback welcome** - "Coming Soon" sections invite community input on design

## Current Implementation vs Documentation

**What's Actually Built (✅):**
- Articles: Full CRUD, Markdown editor, categories, featured photos, draft/publish workflow
- Photos: Full CRUD, Spatie MediaLibrary integration, image conversions, captions
- Categories: Full CRUD, article relationships
- Admin Panel: Dashboard, article/photo/category management
- Authentication: Laravel Fortify with custom UI, email verification, 2FA columns
- CLI Installer: Fully working with interactive/non-interactive modes

**What's Documented But Missing (🚧):**
- Notes: Model, controller, views, routes - completely missing
- Tags: Model missing, no polymorphic tagging system
- Theme System: No themes/ directory, no Folio routing, no Terminal/Starter themes
- Feeds: No RSS/Atom/JSON feed generation
- Microformats: No h-card/h-entry/h-feed markup
- IndieAuth: No authentication endpoints
- Webmentions: Not implemented
- Web Installer UI: Documented terminal UI doesn't exist (only CLI works)
- Component Override System: Documented but not built

**Technical Corrections Needed:**
- Article Editor: Docs may say Editor.js blocks (`content_json`), actually stores Markdown in `content` column
- Photo Storage: Uses Spatie MediaLibrary (not simple file storage)
- Routing: Traditional controller routes (not Folio-based theme routing)
