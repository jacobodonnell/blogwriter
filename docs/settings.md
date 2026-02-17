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

See the [Appearance](/docs/customization/appearance) guide for details on available themes and fonts.

## Site Configuration

Site-level settings (site name, tagline, domain, author info) are configured during installation and stored in the `.env` file.

To change these values after installation, edit `.env` directly:

```env
SITE_NAME="My Blog"
SITE_DOMAIN="https://blog.example.com"
SITE_TAGLINE="Thoughts on tech and life"

AUTHOR_NAME="Jane Smith"
AUTHOR_BIO="Writer and developer"
AUTHOR_EMAIL="jane@example.com"
```

These values are accessible in Blade templates via helper functions:

```blade
{{ site('name') }}
{{ site('domain') }}
{{ author('name') }}
{{ author('bio') }}
```

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

#### [Up Next: *Feeds & IndieWeb*](/docs/configuration/feeds-and-indieweb)
