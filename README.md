# BlogWriter

**Own your content. Own your domain.**

Remember when *we* owned the internet? Before every blog post lived on someone else's platform, behind someone else's algorithm, earning someone else's ad revenue?

BlogWriter is a personal blogging platform you install on your own server. No platform fees. No content policies you didn't write. No risk of your work disappearing because a company pivoted. Your words, your photos, your domain — yours.

## Why BlogWriter?

Enshittification comes for all SaaS apps that survive long enough. Substack takes 10% of your revenue. Patreon takes 8% plus processing fees. And that's while they're being nice — wait until the VCs demand returns and your platform turns on you.

BlogWriter is the open-source alternative for people who want to own their social presence instead of renting under a digital fiefdom.

- **No platform fees, ever** — You only pay your payment processor's standard rates. Everything else is yours.
- **Your words, in your hands** — One-click export of all posts as Markdown. Your writing never lives in just one place.
- **People who follow you** — Export your subscriber list anytime. These are relationships, not assets.
- **Yours forever** — MIT licensed. Even if BlogWriter disappeared tomorrow, you'd still have everything.

## Features

**Three ways to publish:**

- **Articles** — Long-form posts with real-time preview Markdown editor and categories
- **Photos** — Image posts with captions, alt text, and professional image processing
- **Notes** — Short posts (coming soon)

**Simple by design:**

- SQLite database — no MySQL server to manage
- Single admin user — it's your blog, not a multi-tenant platform
- One Artisan command to install
- Runs on any PHP 8.4+ host, including cheap shared hosting

### Indieweb Compatible by Design (coming soon):

- RSS, Atom, and JSON feeds
- Microformats markup (h-card, h-entry, h-feed)
- IndieAuth — sign in anywhere with your own domain
- Webmention support

## Quick Start

```bash
# Clone the repository
git clone https://github.com/jacobodonnell/blogwriter.git
cd blogwriter

# Install dependencies
composer install
npm install && npm run build

# Run the installer
php artisan blogwriter:install
```

The installer checks requirements, creates your database, sets up your admin account, and configures your site. You're writing in minutes.

**Requirements:** PHP 8.4+, that's it. No separate database server. No Redis. No Node.js in production.

## Tech Stack

| Layer | Technology | Why |
|-------|-----------|-----|
| Backend | Laravel 12 | Modern PHP framework with great DX |
| Database | SQLite | Single file, zero config, runs anywhere |
| Frontend | Alpine.js + Alpine AJAX | Reactive without the complexity |
| CSS | Tailwind CSS v4 + DaisyUI | Utility-first with pre-built components |
| Auth | Laravel Fortify | Headless auth, custom UI |
| Media | Spatie MediaLibrary | Professional image handling and conversions |
| Testing | Pest 4 | Expressive, readable tests |

## Documentation

Full documentation is available in the [`docs/`](docs/) directory:

- [Introduction](docs/introduction.md) — Vision and overview
- [Installation](docs/installation.md) — Get BlogWriter running on your server
- [Writing Content](docs/writing-content.md) — Create articles, photos, and notes
- [Themes](docs/themes.md) — Customize how your blog looks
- [Settings](docs/settings.md) — Configure your site and author info
- [Architecture](docs/architecture.md) — Technical overview for contributors
- [Roadmap](docs/roadmap.md) — What's built, what's next

## Roadmap

BlogWriter is in active development toward V0.1 — a working personal blog for daily use.

**Built:** Articles, Photos, Categories, Dashboard, CLI Installer, Auth, MediaLibrary integration

**Next:** Notes, Tags, Feeds, IndieWeb basics, Theme system

**Future:** Paid subscriptions, import from other platforms, plugin marketplace

See the full [Roadmap](docs/roadmap.md) for details.

## Contributing

Contributions are welcome! Before submitting a PR:

1. Run tests: `php artisan test`
2. Run formatter: `vendor/bin/pint`
3. Read the [Architecture](docs/architecture.md) doc to understand the codebase

**Philosophy:** Simple over clever. Explicit over magical. Standards over custom protocols. Less code over more features.

## Standing on the Shoulders of Giants

BlogWriter exists because of the open-source community. Special thanks to the creators and maintainers of the tools that make this project possible:

- **[Laravel](https://laravel.com)** — Taylor Otwell and the Laravel team for building the best PHP framework in the world
- **[Alpine.js](https://alpinejs.dev)** — Caleb Porzio for proving you don't need a heavy JavaScript framework to build great UIs
- **[Alpine AJAX](https://alpine-ajax.js.org)** — Christian Taylor for making dynamic content updates feel effortless
- **[Tailwind CSS](https://tailwindcss.com)** — Adam Wathan and the Tailwind Labs team for changing how we think about styling
- **[Pest](https://pestphp.com)** — Nuno Maduro for making PHP testing actually enjoyable
- **[Spatie](https://spatie.be)** — The Spatie team for MediaLibrary and countless packages that make Laravel better for everyone
- **[DaisyUI](https://daisyui.com)** — Pouya Saadeghi for beautiful, accessible components

## Inspirations

This project is inspired by people and organizations who chose openness when they could have chosen control:

- **Steve Wozniak** — Who believed computers should be in people's hands, not corporations'
- **Linus Torvalds** — Who gave away the kernel that runs the internet
- **DHH** — Who open-sourced Rails and keeps fighting against the complexity industrial complex
- **The Blender Foundation & Community** — Who proved a community-funded open-source tool can stand toe-to-toe with billion-dollar proprietary software

The internet should belong to the people using it. BlogWriter is one small part of making that true again.

## Support the Project

BlogWriter is free and open source. If you believe in owning your presence instead of renting it:

- [Ko-fi](https://ko-fi.com)
- [Buy Me a Coffee](https://www.buymeacoffee.com)
- Star this repo
- Tell a friend

## License

BlogWriter is open-source software licensed under the [MIT License](LICENSE).

---

*You are a person, not a brand. Start writing like one.*