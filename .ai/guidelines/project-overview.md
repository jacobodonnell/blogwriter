=== project overview ===

# BlogWriter

IndieWeb-native blogging platform. Own your content. Own your domain.

## Vision

"Remember when *we* owned the internet?"

Make owning your content as easy as using Substack—without platform fees or platform risk.

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.4+)
- **Database:** SQLite
- **Authentication:** Laravel Fortify (custom implementation)
- **Frontend:** Blade + Alpine.js + Alpine AJAX + Tailwind CSS + DaisyUI
- **Editor:** Editor.js for articles, Markdown for notes

## Content Types

- **Articles** - Long-form posts with titles (Editor.js)
- **Notes** - Short posts (Markdown)
- **Photos** - Images with captions

All posts support draft/published workflow, categories, tags, permalinks, and microformats markup.

## Current Goal

Building V0.1 for personal use on Laravel Forge. See `.specs/v01.md` for complete implementation checklist.

**Success metric:** Personal site runs on BlogWriter and validates on IndieWeb validators.

## Plugin Architecture

BlogWriter features an open plugin architecture inspired by the IBM PC's expansion bus model:

- **Open Protocol** - Plugin store API spec is public, anyone can run a store
- **Multiple Stores** - Official (curated) + third-party (open) stores coexist
- **Composer-Based** - Plugins are standard Composer packages
- **Three Install Methods** - UI store, direct Composer, manual filesystem
- **No Lock-In** - Plugins work regardless of store source

See `.ai/guidelines/plugins.md` for complete architecture details.