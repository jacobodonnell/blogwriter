---
title: Settings
description: Configure your BlogWriter profile and appearance.
extends: _layouts.documentation
section: content
category: configuration
order: 6
---

# Settings

BlogWriter settings are accessible from the admin sidebar under **Settings**.

## Profile Settings

Update your account information:

- **Name** — Your display name, shown in your h-card and site footer
- **Email** — Your login email address
- **Password** — Change your admin password

Profile changes are saved to the User model.

## Appearance Settings

Customize your blog's visual style:

- **Light theme** — Choose from 21 light DaisyUI themes
- **Dark theme** — Choose from 14 dark DaisyUI themes
- **Font** — Choose from 11 fonts across 4 categories

See the [Appearance](appearance.md) guide for details on available themes and fonts.

## Site Settings

Site-level settings (site name, tagline, domain, and author info) are managed via the **Site Settings** tab in the admin settings panel. These values are stored in the `settings` database table via the `Setting` model.

## Robots.txt Settings

Manage your site's `robots.txt` file from **Settings → Robots.txt** in the admin panel. The content is stored in the database and served dynamically via `RobotsController` at `/robots.txt`.

This lets you control search engine crawling behavior without editing files on the server.

## Developer Settings

For developers who prefer working with configuration files:

**Config files:**
- `config/appearance.php` — Available themes, fonts, and defaults

**Clear config cache after changes:**

```bash
php artisan config:clear
```

## Maintenance Commands

- `php artisan blogwriter:user:reset-password` — Reset your admin password via SSH
- `php artisan blogwriter:diagnose` — Run health checks to identify common issues
- `php artisan blogwriter:uninstall` — Full reset (destructive — removes database and configuration)

#### [Up Next: *Feeds & IndieWeb*](feeds-and-indieweb.md)
