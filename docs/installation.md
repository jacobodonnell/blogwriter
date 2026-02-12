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

### Web Installer 🚧 (Coming Soon)

> **🚧 Web Installer UI Not Yet Available**
>
> The fancy terminal-styled web installer shown in the documentation below is planned but not yet implemented. Currently, visiting `/install` will show a message directing you to use the CLI installer above. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues) on web installer design.

**Planned Feature:**

Upload BlogWriter's files to your web server, then visit your domain in a browser:

```
https://yourdomain.com/install
```

You'll see a terminal-styled wizard — dark background, monospace font, box-drawing borders. It looks like a terminal but
runs in your browser, with buttons and form fields you can click.

**Current State:** Web route exists but shows CLI installer instructions.

---

## Installation Steps

> **Note:** The visual installer screens below represent the **planned web installer UI** (coming soon). The **CLI installer** (currently available) walks through the same steps using Laravel Prompts in your terminal.

The CLI installer (working now) and web installer (coming soon) both cover these steps:

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
  The lock file is `storage/installed.lock`. If you need to reinstall during development, delete
  this file first with `rm storage/installed.lock`.
</x-callout>

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

**Accidentally locked out** — Delete `storage/installed.lock` and re-run the installer. Your existing database won't be
overwritten unless you choose to reset it.

#### [Up Next: *Writing Content*](/docs/content/writing-content)
