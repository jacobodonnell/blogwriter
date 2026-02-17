---
title: Local Development
description: Set up BlogWriter on your local machine for development and testing.
extends: _layouts.documentation
section: content
category: getting-started
category_order: 1
order: 3
---

# Local Development

Set up BlogWriter on your local machine before deploying to production.

## Who This Is For

Developers who want to:

- Run BlogWriter locally before deploying to Laravel Forge or shared hosting
- Contribute to BlogWriter development
- Test themes, plugins, or customizations locally

## Laravel Herd (Recommended for Mac)

[Laravel Herd](https://herd.laravel.com) is the fastest way to get a PHP development environment running on macOS. It includes PHP, Nginx, and DNS — no configuration needed.

### 1. Install Herd

Download and install Herd from [herd.laravel.com](https://herd.laravel.com). It runs in your menu bar and includes PHP 8.4, Composer, and a local development server.

### 2. Open Terminal

Press `Cmd + Space` to open Spotlight, type **Terminal**, and press Enter.

### 3. Clone or Download BlogWriter

```bash
# Clone with Git
git clone https://github.com/jacobodonnell/blogwriter.git ~/Herd/blogwriter

# Or download the ZIP and extract to ~/Herd/blogwriter
```

### 4. Install Dependencies and Run the Installer

```bash
cd ~/Herd/blogwriter
composer install
npm install && npm run build
php artisan blogwriter:install
```

### 5. Visit Your Site

Herd automatically serves sites from `~/Herd`. Visit:

```
http://blogwriter.test
```

If your project is in a different directory, use `herd link`:

```bash
cd /path/to/blogwriter
herd link blogwriter
```

## Laravel Valet

If you already have [Valet](https://laravel.com/docs/valet) installed:

```bash
git clone https://github.com/jacobodonnell/blogwriter.git blogwriter
cd blogwriter
composer install
npm install && npm run build
valet link blogwriter
php artisan blogwriter:install
```

Visit `http://blogwriter.test`.

## Docker / Laravel Sail

BlogWriter includes Laravel Sail for Docker-based development:

```bash
git clone https://github.com/jacobodonnell/blogwriter.git blogwriter
cd blogwriter
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan blogwriter:install
```

Visit `http://localhost`.

## Manual Setup

If you have PHP 8.4+ and Composer installed:

```bash
git clone https://github.com/jacobodonnell/blogwriter.git blogwriter
cd blogwriter
composer install
npm install && npm run build
php artisan blogwriter:install
php artisan serve
```

Visit `http://localhost:8000`.

## Frontend Development

BlogWriter uses Vite for frontend asset bundling.

**For active frontend work** (with hot module replacement):

```bash
npm run dev
```

**For a full development server** (PHP + Vite together):

```bash
composer run dev
```

**To build assets for production:**

```bash
npm run build
```

<x-callout type="info" title="Not Seeing Changes?">
  If you modify CSS or JavaScript and don't see changes in the browser, make sure `npm run dev` is running,
  or run `npm run build` to compile assets.
</x-callout>

## Building for Deployment

BlogWriter includes a bundle command that packages everything for deployment:

```bash
php artisan blogwriter:bundle
```

This creates a ZIP file containing:

- All application code
- Compiled frontend assets (`npm run build` runs automatically)
- Composer dependencies (production only)
- `.env.example` template

### Deploying to Shared Hosting

1. Upload and extract the ZIP to your web host
2. Point your domain's document root to the `public/` directory
3. SSH into your server and run `php artisan blogwriter:install`

### Deploying to Laravel Forge

1. Create a new site on Forge pointing to your repository
2. Set the web root to `/public`
3. In your deploy script, add: `php artisan blogwriter:install --non-interactive`
