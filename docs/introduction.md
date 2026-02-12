---
title: BlogWriter
description: Own your content. Own your domain. A personal blogging platform you install on your own server.
extends: _layouts.documentation
section: content
category: getting-started
category_order: 1
order: 1
---

# BlogWriter

**Own your content. Own your domain.**

Remember when *we* owned the internet? Before every blog post lived on someone else's platform, behind someone else's algorithm, earning someone else's ad revenue? BlogWriter brings that back.

BlogWriter is a personal blogging platform you install on your own server. No platform fees. No content policies you didn't write. No risk of your work disappearing because a company pivoted. Your words, your photos, your domain — yours.

## Who It's For

BlogWriter is for personal bloggers who want independence. You might be:

- A writer leaving Substack or Medium who wants to own their archive
- A developer who wants a blog that's simple code, not a sprawling CMS
- Someone who cares about owning their content and keeping it portable
- Someone who just wants a blog that works, looks good, and doesn't spy on your readers

You don't need to be technical. If you can upload files to a web host, you can run BlogWriter.

## What You Get

**Three ways to publish:**

- **Articles** ✅ — Long-form posts with Markdown editor and categories (rich block editor coming soon)
- **Notes** 🚧 — Short posts à la Twitter/Bluesky/Threads (coming soon)
- **Photos** ✅ — Image posts with captions, alt text, and professional MediaLibrary handling

**Built for the open web** (coming soon):

- Standards-based feeds that work with any reader (RSS/Atom/JSON coming soon)
- Semantic HTML markup with microformats (h-card, h-entry, h-feed coming soon)
- Sign in anywhere using your own domain with IndieAuth (coming soon)
- Optional Markdown export — enable `.md` URLs to share raw posts (coming soon)

**Themes you control** (coming soon):

- Will ship with the Terminal theme pre-installed and ready to use
- Will include a minimalist starter theme for building your own designs
- Install additional themes with one command (when theme system is built)
- Create your own theme — it will be just a folder of HTML templates
- Currently uses standard Laravel views in `resources/views/`

**Simple by design:**

- ✅ SQLite database — no MySQL server to manage
- ✅ Single admin user — it's your blog, not a multi-tenant platform
- ✅ One Artisan command to install (web wizard coming soon)
- ✅ Runs on any PHP 8.4+ host, including cheap shared hosting

## How It Works

1. **Install** ✅ — Upload to your server and run the CLI installer (`php artisan blogwriter:install`). It checks requirements, creates your database, and sets up your admin account. Web installer UI coming soon.
2. **Configure** ⚠️ — Settings page currently shows read-only environment info. Full settings UI coming soon.
3. **Write** ✅ — Log into your admin dashboard and start publishing articles and photos (notes coming soon).
4. **Own it** ✅ — Your content lives on your server, at your domain. Open web formats (feeds, microformats) coming soon.

## Documentation

- [Installation](/docs/getting-started/installation) — Get BlogWriter running on your server
- [Writing Content](/docs/content/writing-content) — Create articles, notes, and photos
- [Themes](/docs/customization/themes) — Customize how your blog looks
- [Components](/docs/customization/components) — Copy-paste HTML snippets for theme builders
- [Settings](/docs/configuration/settings) — Configure your site, author info, and features
- [Feeds & IndieWeb](/docs/configuration/feeds-and-indieweb) — RSS, Atom, JSON Feed, microformats, IndieAuth
- [Architecture](/docs/advanced/architecture) — Technical overview for contributors

#### [Up Next: *Installation*](/docs/getting-started/installation)
