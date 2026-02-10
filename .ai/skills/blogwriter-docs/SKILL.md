---
name: blogwriter-docs
description: Reference BlogWriter documentation and specifications when building the platform.
---

# BlogWriter Documentation Reference

Fetch documentation from https://blogwriter-docs.jacobodonnell.me/ to understand BlogWriter specifications while actively building the platform.

## Important Note on Components
**The components documented on the website are SPECIFICATIONS TO BE BUILT, not existing implementations.** The components page shows the planned component library with intended functionality. The actual components must be implemented in this Laravel project.

## Project Overview
Fetch for high-level understanding and philosophy:
- https://blogwriter-docs.jacobodonnell.me/docs/getting-started/introduction - Product vision, target audience, three content types (Articles, Notes, Photos)

## Building Models & Content System
Fetch when implementing Article, Note, Photo models and their relationships:
- https://blogwriter-docs.jacobodonnell.me/docs/content/writing-content - Content type specifications and requirements
- https://blogwriter-docs.jacobodonnell.me/docs/advanced/architecture - Models, relationships, database structure, and content storage approach

## Building the Theme System
Fetch when implementing theme discovery, activation, and Folio routing:
- https://blogwriter-docs.jacobodonnell.me/docs/customization/themes - Theme folder structure, theme.json format, Laravel Folio routing patterns, available template variables ($article, $note, $photo, $category, $tag)

## Building Components
Fetch when implementing the planned component library:
- https://blogwriter-docs.jacobodonnell.me/docs/customization/components - **Planned component specifications.** Includes intended functionality for 17 components (Navbar, Article Card, Note Card, Photo Card, h-card, h-entry, h-feed, Dark Mode Toggle, etc.). Use this as a specification for what needs to be built.

## Building Admin & Settings
Fetch when implementing the admin dashboard and configuration:
- https://blogwriter-docs.jacobodonnell.me/docs/configuration/settings - Site settings, author settings, theme selection, feed configuration, email setup (Resend/Postmark), IndieAuth toggle
- https://blogwriter-docs.jacobodonnell.me/docs/configuration/feeds-and-indieweb - RSS/Atom/JSON feeds, microformats (h-card, h-entry, h-feed), IndieAuth server endpoints

## Building Installation System
Fetch when implementing the web and CLI installers:
- https://blogwriter-docs.jacobodonnell.me/docs/getting-started/installation - PHP 8.4+ requirements, SQLite, 5-step terminal-styled web installer, CLI installer with Laravel Prompts

## Technical Architecture Reference
Fetch for comprehensive technical decisions and stack information:
- https://blogwriter-docs.jacobodonnell.me/docs/advanced/architecture - Complete tech stack (Laravel 12, PHP 8.4+, SQLite, Alpine.js v3, Tailwind v4, DaisyUI v5, Pest 4), architectural philosophy (SQLite only, no Livewire, single-author), directory structure, authentication flow, security considerations
