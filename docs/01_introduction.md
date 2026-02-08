# BlogWriter

**Own your content. Own your domain.**

Remember when *we* owned the internet? Before every blog post lived on someone else's platform, behind someone else's algorithm, earning someone else's ad revenue? BlogWriter brings that back.

BlogWriter is a personal blogging platform you install on your own server. No platform fees. No content policies you didn't write. No risk of your work disappearing because a company pivoted. Your words, your photos, your domain — yours.

## Who It's For

BlogWriter is for personal bloggers who want independence. You might be:

- A writer leaving Substack or Medium who wants to own their archive
- A developer who wants a blog that's simple code, not a sprawling CMS
- Someone who cares about RSS feeds, microformats, and the open web
- Someone who just wants a blog that works, looks good, and doesn't spy on your readers

You don't need to be technical. If you can upload files to a web host, you can run BlogWriter.

## What You Get

**Three ways to publish:**

- **Articles** — Long-form posts with a rich editor, categories, and SEO fields
- **Notes** — Short posts à la Twitter/Bluesky/Threads
- **Photos** — Image posts with captions, alt text, and easy to display and tag galleries

**Built for the open web:**

- RSS, Atom, and JSON Feed — all generated automatically
- Microformats2 markup (h-card, h-entry, h-feed) on every page
- IndieAuth server so you can sign in to other services with your domain
- Optional Markdown export — enable `.md` URLs to share raw Markdown versions of your posts

**Themes you control:**

- Ships with the Terminal theme pre-installed and ready to use
- Includes a minimalist starter theme for building your own designs
- Install additional themes with one command
- Create your own theme — it's just a folder of HTML templates
- Non-technical? Edit some HTML and Tailwind classes. Developer? Set up your own build pipeline.

**Simple by design:**

- SQLite database — no MySQL server to manage
- Single admin user — it's your blog, not a multi-tenant platform
- One Artisan command or web wizard to install
- Runs on any PHP 8.4+ host, including cheap shared hosting

## How It Works

1. **Install** — Upload to your server and run the installer (web or CLI). It checks requirements, creates your database, and sets up your admin account.
2. **Configure** — Set your site name, tagline, and author info. Pick a theme.
3. **Write** — Log into your admin dashboard and start publishing. Articles, notes, photos — whatever you want.
4. **Own it** — Your content lives on your server, at your domain, in formats the open web understands.

## Documentation

- [Installation](02_installation.md) — Get BlogWriter running on your server
- [Writing Content](03_writing-content.md) — Create articles, notes, and photos
- [Themes](04_themes.md) — Customize how your blog looks
- [Components](05_components.md) — Copy-paste HTML snippets for theme builders
- [Settings](06_settings.md) — Configure your site, author info, and features
- [Feeds & IndieWeb](07_feeds-and-indieweb.md) — RSS, Atom, JSON Feed, microformats, IndieAuth
- [Architecture](08_architecture.md) — Technical overview for contributors

#### [Up Next: *Installation*](02_installation.md)
