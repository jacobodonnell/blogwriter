---
title: Appearance
description: Customize your blog's theme and font with 35 DaisyUI themes and 11 fonts.
extends: _layouts.documentation
section: content
category: customization
category_order: 3
order: 4
---

# Appearance

BlogWriter's appearance settings let you change your blog's visual style without editing code. Choose from 35 built-in DaisyUI themes and 11 fonts.

Access appearance settings from **Admin > Settings > Appearance**.

## Themes

BlogWriter includes all 35 built-in DaisyUI 5 themes, split by color scheme.

### Light Themes (21)

light, cupcake, bumblebee, emerald, corporate, retro, cyberpunk, valentine, garden, lofi, pastel, fantasy, wireframe, cmyk, autumn, acid, lemonade, winter, nord, caramellatte, silk

### Dark Themes (14)

dark, synthwave, halloween, forest, aqua, black, luxury, dracula, business, night, coffee, dim, sunset, abyss

### How Theme Selection Works

You set two themes: one for light mode and one for dark mode. BlogWriter supports three-way cycling:

- **Light** — Uses your selected light theme
- **Dark** — Uses your selected dark theme
- **System** — Follows your device's color scheme preference

Hover over any theme in the settings to preview it before committing.

### Defaults

- Light theme: `lofi`
- Dark theme: `dracula`

## Fonts

11 fonts are available, organized into 4 categories:

### Sans-Serif

- Noto Sans (default)
- Nunito
- Inter
- Poppins
- Work Sans

### Admin UI

- Instrument Sans

### Monospace

- JetBrains Mono

### Pixel / Retro

- Press Start 2P
- Pixelify Sans
- VT323
- Silkscreen

## Configuration

Appearance settings are stored via the `Setting` model in the database. The available themes and fonts are defined in `config/appearance.php`.

The config file defines:

- `themes_light` — Array of light theme names
- `themes_dark` — Array of dark theme names
- `fonts` — Key-value pairs mapping CSS variable suffixes to display names
- `font_categories` — Groups fonts by category for the settings UI
- `defaults` — Default light theme, dark theme, and font

## What's Coming

A full theme system with custom templates and theme overrides is on the [Roadmap](/docs/advanced/roadmap).

#### [Up Next: *Settings*](/docs/configuration/settings)
