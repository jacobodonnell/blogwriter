---
name: blogwriter-docs
description: Reference BlogWriter documentation and specifications when building the platform.
---

# BlogWriter Documentation Reference

Access BlogWriter documentation from the local `docs/` directory to understand specifications and implementation status while actively building the platform.

## Critical Implementation Note

**Some features documented are specifications for future implementation, not current state.** Documentation includes both:
- ✅ **Implemented features** - Articles, Photos, Categories, Admin, Authentication, CLI Installer, Feeds, Microformats, Import/Export, Appearance System, PWA
- 🚧 **Coming Soon features** - Notes, Tags, Themes, IndieWeb protocols, Web Installer UI

**Always verify actual codebase implementation status before claiming a feature exists.**

## Documentation Files

All files use YAML frontmatter (HydePHP-compatible format for future static site generation):

### Getting Started
- **`introduction.md`** - Product vision, target audience, content types (Articles, Photos, Notes*)
- **`installation.md`** - CLI installer (✅ working), Web installer UI (🚧 coming soon)
- **`roadmap.md`** - Feature status, V0.1 progress, and future milestones

### Content & Writing
- **`writing-content.md`** - Tiptap WYSIWYG editor with Markdown storage (✅), Notes (🚧 coming soon), Photos (✅), Tags (🚧 coming soon)

### Customization (Planned)
- **`themes.md`** - Theme system specification (🚧 coming soon - entire system not yet built)
- **`components.md`** - Component library (stub, needs content or specification)

### Configuration
- **`settings.md`** - Settings UI (✅ functional)
- **`feeds-and-indieweb.md`** - RSS/Atom/JSON feeds (✅), microformats (✅), IndieAuth (🚧 coming soon), Webmentions (🚧 coming soon)

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

**What's Documented But Missing (🚧):**
- Notes: Model, controller, views, routes — not yet built
- Tags: Polymorphic tagging system — not yet built
- Theme System: Custom template/override system — not yet built
- IndieAuth: Authorization/token/metadata endpoints — not yet built
- Webmentions: Send and receive — not yet built
- Web Installer UI: Only CLI installer works
- Component Override System: Documented but not built
