---
title: Installation
description: Get BlogWriter running on your server in a few minutes.
extends: _layouts.documentation
section: content
category: getting-started
category_order: 1
order: 2
---

# Installation

Get BlogWriter running on your server in a few minutes.

<x-callout type="info" title="Local Development?">
  If you're setting up BlogWriter on your own machine for development or testing, see the
  [Local Development](/docs/getting-started/local-development) guide instead.
</x-callout>

## Requirements

BlogWriter works on most modern web hosting. You need:

- **PHP 8.4 or newer** — Most hosts support this now
- **A web server** — Shared hosting, VPS, or cloud hosting all work
- **About 100 MB of disk space** — For the application and your content

That's it. No separate database server to set up. No Redis. No Node.js. BlogWriter uses SQLite, which means your
database is just a simple file on your server.

BlogWriter does not send or receive email out of the box — no mail server needed. Your email address is used as your
login credential and public identity (h-card), not for sending mail.

<x-callout type="info" title="Technical Note" collapsible>
  BlogWriter requires PHP 8.4+ with the SQLite and PDO extensions enabled, plus write permissions on
  the `storage/` and `bootstrap/cache/` directories.
</x-callout>

## CLI Installer

If you have SSH access to your server (or access to the web-based terminal in Laravel Forge), use the CLI installer:

```bash
php artisan blogwriter:install
```

The installer uses Laravel Prompts for an interactive terminal experience:

- Checks all system requirements
- Creates `.env` file and generates `APP_KEY`
- Sets up SQLite database
- Runs all migrations
- Creates your admin account
- Seeds initial data
- Configures site settings

**Non-interactive mode** is also available for automated deployments:

```bash
php artisan blogwriter:install --non-interactive
```

<x-callout type="info" title="Install Page">
  If you visit your site before running the installer, you'll see a page at `/install` with instructions to run the CLI command.
</x-callout>

---

## What the Installer Does

Behind the scenes, the CLI installer:

1. **Checks requirements** — Verifies PHP version, SQLite extension, PDO extension, and directory permissions
2. **Sets up the environment** — Copies `.env.example` to `.env` (if needed) and generates an `APP_KEY`
3. **Creates the database** — Creates the SQLite database file at `database/database.sqlite` and runs all migrations
4. **Creates your admin account** — Prompts for your email and password (email doubles as your public identity for
   h-card markup)
5. **Configures your site** — Sets site name, domain, tagline, and author information in `.env`
6. **Seeds initial data** — Creates default categories and other starter content
7. **Locks the installation** — Creates `storage/installed.lock` to prevent accidental reinstallation

---

## Installation Lock

After installation completes, BlogWriter creates a lock file that prevents anyone from running the installer again and
overwriting your configuration. This protects your site from accidental reinstallation.

<x-callout type="info" title="For Developers" collapsible>
  The lock file is `storage/installed.lock`. If you need to reinstall during development, run
  the installer again and choose "Yes" when prompted to reset.
</x-callout>

## Install as a Web App

BlogWriter is a Progressive Web App (PWA). You can install it on your device for a native app-like experience —
full-screen
window, faster loads, and a home screen icon.

### iOS / iPadOS

1. Open your BlogWriter site in **Safari**
2. Tap the **Share** button (square with an arrow)
3. Tap **Add to Home Screen**
4. Name the app and tap **Add**

<x-callout type="warning" title="Safari Required">
  On iOS and iPadOS, only Safari supports installing web apps. Chrome, Firefox, and other browsers won't show the option.
</x-callout>

### Mac

**Safari (macOS Sonoma or later):**

1. Open your BlogWriter site in Safari
2. Go to **File → Add to Dock**
3. Name the app and click **Add**

**Chrome and other Chromium browsers:**

1. Open your BlogWriter site
2. Click the **install icon** in the address bar (or go to the browser menu → "Install BlogWriter…")
3. Click **Install**

### What You Get

- **Full-screen window** — no browser chrome, feels like a native app
- **Home screen / Dock icon** — launch BlogWriter like any other app
- **Offline page** — a friendly fallback when you lose connectivity
- **Theme-aware chrome** — the title bar matches your site's theme color

## After Installation

- **Visit your site** at your domain
- **Log in** at `/admin` with the email and password you chose
- **Start writing** — see the [Writing Content](/docs/content/writing-content) guide
- **Customize your appearance** — see the [Appearance](/docs/customization/appearance) guide

## Troubleshooting

**"PHP version too old"** — BlogWriter requires PHP 8.4 or newer. Check with `php -v`. Contact your hosting provider
about upgrading.

**"SQLite extension not found"** — Your PHP installation needs the `pdo_sqlite` extension. On most hosts, this is
enabled by default. On Ubuntu/Debian: `sudo apt install php8.4-sqlite3`.

**"Storage not writable"** — BlogWriter needs to write to `storage/` and `bootstrap/cache/`. Fix permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

**"Install page shows 404"** — Make sure your web server points to BlogWriter's `public/` directory, not the project
root.

**Forgot your password?** — Run `php artisan blogwriter:user:reset-password` via SSH to reset your admin password.

**Something not working?** — Run `php artisan blogwriter:diagnose` for health checks that identify common issues.

**Accidentally locked out** — Re-run the installer (`php artisan blogwriter:install`). When prompted, choose "Yes" to
reset if you want a fresh start, or "No" to cancel if your data is still accessible.

#### [Up Next: *Writing Content*](/docs/content/writing-content)
