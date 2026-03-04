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
- **Editor:** Tiptap with tiptap-markdown extension for articles

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

- **Articles** - Long-form posts with titles (Tiptap WYSIWYG editor)
- **Photos** - Images with captions
- **Pages** - Static pages (home, about) with layout choices (V0.2)
- **Notes** - Short posts (V0.3)

All posts support draft/published workflow, categories, tags, permalinks, and microformats markup.

## Current Goal

V0.1 is live and deployed on Laravel Forge. V0.2 focuses on subscribers, monetization, and newsletter integration — making BlogWriter a viable Substack/Patreon alternative. See `docs/roadmap.md` for the full roadmap.

## Future Plans

Plugin architecture planned for future versions. Laravel Cashier (Stripe) and Buttondown newsletter integration are part of V0.2.

