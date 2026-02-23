---
title: Feeds & IndieWeb
description: Microformats markup, RSS/Atom/JSON feeds, and IndieWeb protocol support.
extends: _layouts.documentation
section: content
category: configuration
order: 7
---

# Feeds & IndieWeb

BlogWriter implements IndieWeb microformats markup throughout its templates. Feed generation and additional IndieWeb protocols are planned.

---

## Microformats: Implemented

BlogWriter's templates include Microformats2 markup, making your content machine-readable using semantic HTML classes.

### h-card: Your Identity

An h-card is a virtual business card embedded in your site. BlogWriter renders h-card markup in:

- **Site footer** — Your name, avatar, bio, and rel="me" links
- **Profile page** — Full h-card with all author information

```html
<div class="h-card">
    <img src="/avatar.jpg" alt="Your Name" class="u-photo">
    <a href="https://yourdomain.com" class="u-url p-name" rel="me">Your Name</a>
    <p class="p-note">Writer, developer, human.</p>
</div>
```

### h-entry: Your Posts

Every article and photo is wrapped in h-entry markup:

- **Articles** have a title (`p-name`), content (`e-content`), published date (`dt-published`), and author
- **Photos** have an image (`u-photo`), optional caption, and published date

```html
<article class="h-entry">
    <h1 class="p-name">My Article Title</h1>
    <time class="dt-published" datetime="2026-01-31">January 31, 2026</time>
    <div class="e-content">
        <p>Article content goes here...</p>
    </div>
</article>
```

### h-feed: Your Post Lists

Post listing pages are wrapped in h-feed markup, identifying them as feeds of h-entry items. This is implemented on:

- Home page
- Articles index
- Photos index
- Category pages

```html
<div class="h-feed">
    <span class="p-name hidden">My Blog</span>
    <article class="h-entry"><!-- post 1 --></article>
    <article class="h-entry"><!-- post 2 --></article>
</div>
```

### rel="me" Links

BlogWriter outputs `rel="me"` links for identity verification across services. These are rendered in the site footer and profile page for:

- GitHub
- Mastodon
- Bluesky
- Email

These links allow IndieWeb services and social platforms to verify that your profiles belong to the same person.

### Validation

You can verify your microformats implementation using:

- [IndieWebify.me](https://indiewebify.me) — Checks h-card, h-entry, and feed discovery
- [php-mf2 parser](https://php.microformats.io) — See what machines see
- [pin13.net parser](https://pin13.net/mf2/) — Paste your URL, see structured data

---

## Feeds: Implemented

BlogWriter generates three feed formats from your published articles and photos.

| Feed      | URL                                 | Format        |
|-----------|-------------------------------------|---------------|
| RSS       | `/feed` (or `/rss`)                 | RSS 2.0       |
| Atom      | `/atom`                             | Atom 1.0      |
| JSON Feed | `/feed.json`                        | JSON Feed 1.1 |

All feeds include full content (not excerpts) for the 20 most recent published items across articles and photos, sorted chronologically.

### Feed Discovery

Feed discovery `<link>` tags are included in the HTML `<head>` for all public pages, enabling feed readers to automatically detect your feeds.

---

## IndieAuth: Planned

IndieAuth will let you sign in to websites by proving you control your domain. Your domain becomes your identity, and your BlogWriter installation becomes your identity provider.

When implemented, BlogWriter will provide authorization, token, and metadata endpoints automatically.

---

## Webmentions: Planned

Webmentions are a protocol for sites to notify each other when they link to one another. When implemented, BlogWriter will support both sending and receiving webmentions.

---

## Summary

| Feature                                | Status      |
|----------------------------------------|-------------|
| Microformats (h-card, h-entry, h-feed) | Implemented |
| rel="me" identity links                | Implemented |
| RSS/Atom/JSON Feeds                    | Implemented |
| Feed discovery `<link>` tags           | Implemented |
| IndieAuth                              | Planned     |
| Webmentions                            | Planned     |

#### [Up Next: *Architecture*](architecture.md)
