# BlogWriter

**Own your content. Own your domain.**

BlogWriter is a personal blogging platform you install on your own server. Your words, your photos, your domain. No
platform fees, no content policies you didn't write, no risk of your work disappearing because a company pivoted.

## Who It's For

BlogWriter is for personal bloggers who want independence:

- Writers leaving Substack or Medium who want to own their archive
- Developers who want a blog that's simple code, not a sprawling CMS
- Anyone who cares about owning their content and keeping it portable

You'll need basic comfort with a terminal — copying and pasting a few commands, and optionally SSH access to your
server. If that sounds doable, you can run BlogWriter.

## What You Get

**Two ways to publish:**

- **Articles** — Long-form posts with a Tiptap WYSIWYG editor, live preview customizer, categories, featured images, and
  permalink redirects
- **Photos** — Your own media library, stored on your server. Upload images with captions, alt text, and EXIF display.
  Draft photos stay private until you publish; published photos are served directly from your server. Automatic image
  conversions (thumbnail, medium, large) via Spatie MediaLibrary.
- **Feeds** — RSS 2.0, Atom 1.0, and JSON Feed 1.1 at `/feed`, `/atom`, and `/feed.json`

**Appearance customization:**

- 35 built-in DaisyUI themes (21 light, 14 dark) with hover-to-preview
- 11 fonts across 4 categories (sans-serif, serif, admin UI, monospace)
- Three-way theme cycling: light, dark, and system preference

**Admin panel:**

- Dashboard with article and photo stats
- Sortable, filterable articles table with column toggles and per-page pagination
- Statamic-inspired live preview customizer with resizable split-pane editor
- AJAX auto-save while editing
- Markdown export (ZIP) and import — round-trip your content with full fidelity

**IndieWeb markup:**

- Microformats2 throughout: h-card (footer and profile page), h-entry (articles and photos), h-feed (home, articles
  index, photos index, category pages)
- `rel="me"` links for identity verification (GitHub, Mastodon, Bluesky, Email)

**Simple by design:**

- SQLite database — no MySQL server to manage
- Single admin user — it's your blog, not a multi-tenant platform
- One Artisan command to install
- Runs on any PHP 8.4+ host, including cheap shared hosting
- Progressive Web App — install on your device for an app-like experience

## How It Works

1. **Install** — Upload to your server and run `php artisan blogwriter:install`. The CLI installer checks requirements,
   creates your database, and sets up your admin account.
2. **Configure** — Set your appearance (theme, font) and profile information from the admin panel.
3. **Write** — Log into your admin dashboard and start publishing articles and photos.
4. **Own it** — Your content lives on your server, at your domain, with IndieWeb microformats markup built in.

## What's Coming

See the [Roadmap](roadmap.md) for planned features including Notes, Tags, IndieAuth, and more.

## Documentation

- [Installation](installation.md) — Get BlogWriter running on your server
- [Local Development](local-development.md) — Set up BlogWriter on your local machine
- [Deployment](deployment.md) — Deploy to production with Laravel Forge
- [Writing Content](writing-content.md) — Create articles and photos
- [Appearance](appearance.md) — Customize your theme and font
- [Settings](settings.md) — Configure your site and profile
- [Feeds & IndieWeb](feeds-and-indieweb.md) — Microformats, feeds, and IndieWeb protocols
- [Architecture](architecture.md) — Technical overview for contributors
- [Roadmap](roadmap.md) — What's built, what's next

#### [Up Next: *Installation*](installation.md)
