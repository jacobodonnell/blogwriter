# Installation

Get BlogWriter running on your server in a few minutes.

## Requirements

BlogWriter works on most modern web hosting. You need:

- **PHP 8.4 or newer** — Most hosts support this now
- **A web server** — Shared hosting, VPS, or cloud hosting all work
- **About 50 MB of disk space** — For the application and your content

That's it. No separate database server to set up. No Redis. No Node.js. BlogWriter uses SQLite, which means your database is just a simple file on your server.

> **Technical Note:** BlogWriter requires PHP 8.4+ with the SQLite and PDO extensions enabled, plus write permissions on the `storage/` and `bootstrap/cache/` directories.

## Easiest: One-Click Installers

If your hosting provider supports Installatron or Softaculous, you can install BlogWriter with one click. Look for BlogWriter in your hosting control panel's app installer, click Install, and you're done.

<!--
## Quick Install (VPS)

One command to install BlogWriter with FrankenPHP on a fresh VPS:

```bash
curl -sSL https://blogwriter.io/install.sh | bash
```

This installer will set up FrankenPHP, download BlogWriter, and walk you through the initial configuration.
-->

## Manual Installation

If you don't have a one-click installer, BlogWriter offers two manual installation methods. Both walk you through the same steps — pick whichever fits your setup.

### Web Installer

Upload BlogWriter's files to your web server, then visit your domain in a browser:

```
https://yourdomain.com/install
```

You'll see a terminal-styled wizard — dark background, monospace font, box-drawing borders. It looks like a terminal but runs in your browser, with buttons and form fields you can click.

### CLI Installer

If you have SSH access to your server, you can run the installer from the command line:

```bash
php artisan blogwriter:install
```

This uses Laravel Prompts to give you the same step-by-step flow in your actual terminal.

---

## Installation Steps

Both installers walk through these screens:

### Step 1: Welcome

```
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

```
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

```
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

```
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

```
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

> **Technical Details:** Behind the scenes, the installer copies `.env.example` to `.env` (if it doesn't exist), generates an `APP_KEY`, creates the SQLite database file at `database/database.sqlite`, runs all database migrations, creates your admin user, writes your site configuration to `.env`, activates the Terminal theme, and creates `storage/installed.lock` to prevent re-installation.

## Installation Lock

After installation completes, BlogWriter creates a lock file that prevents anyone from running the installer again and overwriting your configuration. This protects your site from accidental reinstallation.

> **For Developers:** The lock file is `storage/installed.lock`. If you need to reinstall during development, delete this file first with `rm storage/installed.lock`.

## After Installation

- **Visit your site** at your domain to see the default theme
- **Log in** at `/admin` with the email and password you chose
- **Start writing** — see the [Writing Content](writing-content.md) guide
- **Customize your theme** — see the [Themes](themes.md) guide

## Troubleshooting

**"PHP version too old"** — BlogWriter requires PHP 8.4 or newer. Check with `php -v`. Contact your hosting provider about upgrading.

**"SQLite extension not found"** — Your PHP installation needs the `pdo_sqlite` extension. On most hosts, this is enabled by default. On Ubuntu/Debian: `sudo apt install php8.4-sqlite3`.

**"Storage not writable"** — BlogWriter needs to write to `storage/` and `bootstrap/cache/`. Fix permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

**"Install page shows 404"** — Make sure your web server points to BlogWriter's `public/` directory, not the project root.

**Accidentally locked out** — Delete `storage/installed.lock` and re-run the installer. Your existing database won't be overwritten unless you choose to reset it.

#### [Up Next: *Writing Content*](03_writing-content.md)
