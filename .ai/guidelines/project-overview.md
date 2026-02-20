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
- **Editor:** EasyMDE (Markdown) for articles

## DaisyUI

This project uses DaisyUI v5 as its component library on top of Tailwind CSS v4.

### Skills Activation

IMPORTANT: Activate the `daisyui-development` skill when:

- Adding or modifying DaisyUI components (btn, card, modal, alert, badge, table, etc.)
- Building or restyling forms with DaisyUI form elements (fieldset, input, select, file-input)
- Working with navigation components (navbar, menu, dropdown, breadcrumbs, tabs)
- Implementing modals or dialog components
- Working with theme switching or `data-theme`
- The user mentions DaisyUI, component library, or themed UI components

## Content Types

- **Articles** - Long-form posts with titles (EasyMDE Markdown editor)
- **Notes** - Short posts (planned)
- **Photos** - Images with captions

All posts support draft/published workflow, categories, tags, permalinks, and microformats markup.

## Current Goal

Building V0.1 for personal use on Laravel Forge. See `.specs/v01.md` for complete implementation checklist.

**Success metric:** Personal site runs on BlogWriter and validates on IndieWeb validators.

## Plugin Architecture (Planned)

BlogWriter's planned plugin architecture is inspired by the IBM PC's expansion bus model. **This is a design specification — not yet implemented.**

- **Open Protocol** - Plugin store API spec will be public, anyone can run a store
- **Multiple Stores** - Official (curated) + third-party (open) stores will coexist
- **Composer-Based** - Plugins will be standard Composer packages
- **Three Install Methods** - UI store, direct Composer, manual filesystem
- **No Lock-In** - Plugins will work regardless of store source

See `.specs/plugins.md` for complete architecture details.