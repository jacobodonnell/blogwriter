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
- **About 50 MB of disk space** — For the application and your content

That's it. No separate database server to set up. No Redis. No Node.js. BlogWriter uses SQLite, which means your
database is just a simple file on your server.

<x-callout type="info" title="Technical Note" collapsible>
  BlogWriter requires PHP 8.4+ with the SQLite and PDO extensions enabled, plus write permissions on
  the `storage/` and `bootstrap/cache/` directories.
</x-callout>

## Easiest: One-Click Installers

<x-callout type="planned" title="One-Click Installers (Coming Soon)" collapsible>
If your hosting provider supports Installatron or Softaculous, you'll be able to install BlogWriter with one click. Look for
BlogWriter in your hosting control panel's app installer, click Install, and you're done.
</x-callout>

## Quick Install on VPS

<x-callout type="planned" title="Quick Install on VPS (Coming Soon)" collapsible>
One command to install BlogWriter with FrankenPHP on a fresh VPS:

```bash
curl -sSL https://blogwriter.io/install.sh | bash
```

This installer will set up FrankenPHP, download BlogWriter, and walk you through the initial configuration.
</x-callout>

## Manual Installation

### CLI Installer ✅ (Recommended - Fully Working)

If you have SSH access to your server, use the CLI installer:

```bash
php artisan blogwriter:install
```

This fully-functional installer uses Laravel Prompts for an interactive terminal experience:

- ✅ Checks all system requirements
- ✅ Creates `.env` file and generates `APP_KEY`
- ✅ Sets up SQLite database
- ✅ Runs all migrations
- ✅ Creates your admin account
- ✅ Seeds initial data
- ✅ Configures site settings

**Non-interactive mode** is also available for automated deployments:

```bash
php artisan blogwriter:install --non-interactive
```

### Web Installer ✅ (For Shared Hosting)

If you don't have SSH access, upload BlogWriter's files to your web server and visit your domain in a browser:

```
https://yourdomain.com/install
```

You'll see a terminal-styled wizard — dark background, JetBrains Mono font, and progressive terminal output. It looks like a terminal but runs entirely in your browser with real form validation.

The web installer walks through the same steps as the CLI installer: requirements check, account creation, site configuration, database setup, and optional demo content seeding.

---

## Installation Steps

Both the CLI installer and web installer cover these steps:

### Step 1: Welcome

```text
┌─────────────────────────────────────────────────────────────┐
│                        BlogWriter                           │
│                Own Your Content. Own Your Domain.            │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ Installation Wizard                                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Welcome to BlogWriter!                                     │
│                                                             │
│  This wizard will help you set up your blog in a few steps. │
│                                                             │
│  What we'll do:                                             │
│  • Check system requirements                                │
│  • Create your admin account                                │
│  • Configure your site                                      │
│  • Activate your theme                                      │
│                                                             │
│  [Continue]  [Cancel]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

Step 1 of 5
```

### Step 2: Requirements Check

The installer verifies your server meets the requirements.

```text
┌─────────────────────────────────────────────────────────────┐
│ System Requirements                                         │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ✓ PHP 8.4+                    [8.4.17]                     │
│  ✓ SQLite Extension            [Enabled]                    │
│  ✓ PDO Extension               [Enabled]                   │
│  ✓ Storage Writable            [OK]                         │
│  ✓ Cache Writable              [OK]                         │
│                                                             │
│  All required checks passed!                                │
│                                                             │
│  [Continue]  [Back]                                         │
│                                                             │
└─────────────────────────────────────────────────────────────┘

Step 2 of 5
```

Green checkmarks mean you're good. Red X marks mean something needs fixing — the installer tells you what.

### Step 3: Create Admin Account

Your blog has one admin user — you.

```text
┌─────────────────────────────────────────────────────────────┐
│ Create Admin Account                                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Email                                                      │
│  ┌────────────────────────────────────────────────────┐     │
│  │ you@example.com                                    │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  Password                                                   │
│  ┌────────────────────────────────────────────────────┐     │
│  │ ••••••••••••                                       │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  Confirm Password                                           │
│  ┌────────────────────────────────────────────────────┐     │
│  │ ••••••••••••                                       │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  [Continue]  [Back]                                         │
│                                                             │
└─────────────────────────────────────────────────────────────┘

Step 3 of 5
```

### Step 4: Site Configuration

Tell BlogWriter about your site and yourself.

```text
┌─────────────────────────────────────────────────────────────┐
│ Site Configuration                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Site Name                                                  │
│  ┌────────────────────────────────────────────────────┐     │
│  │ My Blog                                            │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  Site Domain                                                │
│  ┌────────────────────────────────────────────────────┐     │
│  │ blog.example.com                                   │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  Tagline                                                    │
│  ┌────────────────────────────────────────────────────┐     │
│  │ Thoughts on tech, life, and everything else        │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  Author Name                                                │
│  ┌────────────────────────────────────────────────────┐     │
│  │ Your Name                                          │     │
│  └────────────────────────────────────────────────────┘     │
│                                                             │
│  [Continue]  [Back]                                         │
│                                                             │
└─────────────────────────────────────────────────────────────┘

Step 4 of 5
```

These values are stored in your `.env` file and can be changed later from Settings in the admin dashboard.

### Step 5: Done

```text
┌─────────────────────────────────────────────────────────────┐
│ Installation Complete!                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ✓ Database created                                         │
│  ✓ Admin account created                                    │
│  ✓ Site configured                                          │
│  ✓ Default theme activated                                  │
│                                                             │
│  Your blog is ready!                                        │
│                                                             │
│  Next steps:                                                │
│  • Visit your site: https://blog.example.com                │
│  • Admin dashboard: https://blog.example.com/admin          │
│  • Write your first post!                                   │
│                                                             │
│  [Visit Site]  [Go to Admin]                                │
│                                                             │
└─────────────────────────────────────────────────────────────┘

Step 5 of 5
```

---

<x-callout type="info" title="Technical Details" collapsible>
  Behind the scenes, the CLI installer copies `.env.example` to `.env` (if it doesn't exist),
  generates an `APP_KEY`, creates the SQLite database file at `database/database.sqlite`, runs all database migrations,
  creates your admin user, writes your site configuration to `.env`, seeds initial data, and creates
  `storage/installed.lock` to prevent re-installation. (Theme activation will be added when the theme system is implemented.)
</x-callout>

## Installation Lock

After installation completes, BlogWriter creates a lock file that prevents anyone from running the installer again and
overwriting your configuration. This protects your site from accidental reinstallation.

<x-callout type="info" title="For Developers" collapsible>
  The lock file is `storage/installed.lock`. If you need to reinstall during development, run
  the installer again and choose "Yes" when prompted to reset.
</x-callout>

## Install as a Web App

BlogWriter is a Progressive Web App (PWA). You can install it on your device for a native app-like experience — full-screen
window, home screen icon, faster loads, and an offline fallback page.

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

- **Visit your site** at your domain to see the default theme
- **Log in** at `/admin` with the email and password you chose
- **Start writing** — see the [Writing Content](/docs/content/writing-content) guide
- **Customize your theme** — see the [Themes](/docs/customization/themes) guide

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

**Accidentally locked out** — Re-run the installer (`php artisan blogwriter:install`). When prompted, choose "Yes" to
reset if you want a fresh start, or "No" to cancel if your data is still accessible.

#### [Up Next: *Writing Content*](/docs/content/writing-content)
