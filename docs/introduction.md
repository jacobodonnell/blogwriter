---
title: BlogWriter
description: A personal blogging platform you install on your own server. Articles, photos, and full ownership of your content.
extends: _layouts.documentation
section: content
category: getting-started
category_order: 1
order: 1
---

# BlogWriter

**Own your content. Own your domain.**

BlogWriter is a personal blogging platform you install on your own server. Your words, your photos, your domain. No platform fees, no content policies you didn't write, no risk of your work disappearing because a company pivoted.

## Who It's For

BlogWriter is for personal bloggers who want independence:

- Writers leaving Substack or Medium who want to own their archive
- Developers who want a blog that's simple code, not a sprawling CMS
- Anyone who cares about owning their content and keeping it portable

You don't need to be technical. If you can upload files to a web host, you can run BlogWriter.

## What You Get

**Two ways to publish:**

- **Articles** — Long-form posts with a Markdown editor (EasyMDE), live preview customizer, categories, featured images, and permalink redirects
- **Photos** — Image posts with captions, alt text, EXIF display, and automatic image conversions via Spatie MediaLibrary

**Appearance customization:**

- 35 built-in DaisyUI themes (21 light, 14 dark) with hover-to-preview
- 11 fonts across 4 categories (sans-serif, admin UI, monospace, pixel/retro)
- Three-way theme cycling: light, dark, and system preference

**Admin panel:**

- Dashboard with article and photo stats
- Sortable, filterable articles table with column toggles and per-page pagination
- WordPress-style customizer with resizable split-pane preview
- AJAX auto-save while editing

**IndieWeb markup:**

- Microformats2 throughout: h-card (footer and profile page), h-entry (articles and photos), h-feed (home, articles index, photos index, category pages)
- `rel="me"` links for identity verification (GitHub, Mastodon, Bluesky, Email)

**Simple by design:**

- SQLite database — no MySQL server to manage
- Single admin user — it's your blog, not a multi-tenant platform
- One Artisan command to install
- Runs on any PHP 8.4+ host, including cheap shared hosting
- Progressive Web App — install on your device for an app-like experience

## How It Works

1. **Install** — Upload to your server and run `php artisan blogwriter:install`. The CLI installer checks requirements, creates your database, and sets up your admin account.
2. **Configure** — Set your appearance (theme, font) and profile information from the admin panel.
3. **Write** — Log into your admin dashboard and start publishing articles and photos.
4. **Own it** — Your content lives on your server, at your domain, with IndieWeb microformats markup built in.

## What's Coming

See the [Roadmap](/docs/advanced/roadmap) for planned features including Notes, Tags, RSS/Atom/JSON feeds, IndieAuth, and more.

## Documentation

- [Installation](/docs/getting-started/installation) — Get BlogWriter running on your server
- [Writing Content](/docs/content/writing-content) — Create articles and photos
- [Appearance](/docs/customization/appearance) — Customize your theme and font
- [Settings](/docs/configuration/settings) — Configure your site and profile
- [Feeds & IndieWeb](/docs/configuration/feeds-and-indieweb) — Microformats, feeds, and IndieWeb protocols
- [Architecture](/docs/advanced/architecture) — Technical overview for contributors
- [Roadmap](/docs/advanced/roadmap) — What's built, what's next

#### [Up Next: *Installation*](/docs/getting-started/installation)
