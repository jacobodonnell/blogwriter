---
title: Components
description: Ready-to-use Blade components for navigation, article cards, microformats, and more.
extends: _layouts.components
section: content
category: customization
order: 5
---

# Components

> **🚧 Component Library Coming Soon**
>
> A comprehensive component library is planned but not yet implemented. This will include ready-to-use Blade components for article cards, note cards, photo cards, navigation, microformats (h-card, h-entry, h-feed), and more. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues) on which components would be most useful.

**Current State:** Basic layout components exist (`layouts/admin`, `layouts/guest`, `layouts/public`, `layouts/base`). Full component library to be built with theme system.

## Planned Component Library

BlogWriter will include ready-to-use Blade components for common elements. Use them as-is in your themes, or override them by creating a file with the same name in your theme's `components/` directory.

### Planned Components

**Content Display:**
- `article-card` - Article preview card for lists/grids
- `note-card` - Note display for streams
- `photo-card` - Photo with caption display
- `article-list` - Full article list with pagination
- `category-badge` - Category pill/badge
- `tag-link` - Tag link component

**Navigation:**
- `navbar` - Main site navigation
- `footer` - Site footer with links
- `breadcrumbs` - Breadcrumb navigation
- `pagination` - Pagination controls

**Microformats:**
- `h-card` - IndieWeb identity card
- `h-entry-article` - Microformatted article
- `h-entry-note` - Microformatted note
- `h-feed` - Microformatted feed container

**UI Elements:**
- `dark-mode-toggle` - Theme switcher
- `search-bar` - Search input component
- `reading-progress` - Progress bar for articles

## Overriding Components (Planned)

When the theme system is implemented, you'll be able to customize any component by creating a file with the same name in your theme's `components/` directory:

```
themes/my-theme/components/article-card.blade.php
```

Copy the component code, modify it, and your version will be used instead of the built-in one.

**Component Priority:**
1. `themes/active-theme/components/component-name.blade.php` (theme override)
2. `resources/views/components/component-name.blade.php` (built-in)

**Current State:** No component override system exists. Theme system needed first.

#### [Up Next: *Settings*](/docs/configuration/settings)
