---
title: Deployment
description: Deploy BlogWriter to a production server using Laravel Forge.
extends: _layouts.documentation
section: content
category: getting-started
category_order: 1
order: 4
---

# Deployment

Deploy BlogWriter to your own server with Laravel Forge — the recommended way to host a Laravel application without managing a full server stack yourself.

**Prerequisites:**

- A [Laravel Forge](https://forge.laravel.com) account with a provisioned PHP 8.4 server
- Your BlogWriter repo pushed to GitHub
- A domain pointed at your server's IP (Step 2 covers this)
- Node 18+ installed on your server (Forge servers include this by default)

---

## Step 1 — Push to GitHub

Create a new repo on GitHub and push your local project:

```bash
git remote add origin https://github.com/yourusername/your-repo.git
git branch -M main
git push -u origin main
```

Confirm the repo is visible on github.com before continuing.

---

## Step 2 — Point DNS at Your Server

In your DNS provider, add A records pointing to your Forge server's IP address:

| Type | Name | Value |
|------|------|-------|
| A | `yourdomain.com` | Your Forge server IP |
| A | `www` | Your Forge server IP |

Do this first — DNS propagation can take up to an hour, and Let's Encrypt SSL (Step 7) requires your domain to be resolving before it can issue a certificate.

<x-callout type="warning" title="Cloudflare Users">
  Start with the orange cloud (proxy) **disabled** — use DNS-only mode — until your SSL certificate is confirmed working. You can re-enable proxying after.
</x-callout>

---

## Step 3 — Create the Site in Forge

1. Forge → your server → **Sites** → **New Site**
2. Domain: `yourdomain.com`
3. Web root: `/public`
4. Project type: **Laravel**
5. Connect your GitHub repo (Forge will prompt you to link your account on first use — public repos need no deploy key)
6. Branch: `main`

---

## Step 4 — Configure Shared Paths

<x-callout type="warning" title="Do This Before Your First Deploy">
  Forge's zero-downtime deployments clone your repo into a fresh directory for every release. Because
  <code>database/database.sqlite</code> is gitignored, each release would start with an empty database —
  wiping all your posts and settings. Shared paths solve this by keeping one canonical copy of your data
  that survives every deploy.
</x-callout>

In Forge: **Site → Settings → Deployments → Shared Paths**, add:

1. `database/database.sqlite`
2. `storage`

Forge keeps a single copy of each path at `{site_root}/path` and symlinks it into every release directory. Your database and uploaded media persist across all future deploys.

<x-callout type="warning" title="Zero-Downtime Deployments Are Set at Site Creation">
  Zero-downtime deployments can only be enabled when creating a new site in Forge — they cannot be added to an existing site. If you skipped this, you'll need to recreate the site.
</x-callout>

<x-callout type="info" title=".env Is Automatically Shared">
  Forge automatically persists your <code>.env</code> file across releases. You don't need to add it to Shared Paths.
</x-callout>

---

## Step 5 — Environment Variables

In Forge: **Site → Environment**, set the following:

```ini
APP_NAME="Your Blog Name"
APP_ENV=production
APP_KEY=                          # Leave blank — the install command generates it
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=error
```

Leave `APP_KEY` blank. The `blogwriter:install` command generates it on the first deploy.

---

## Step 6 — Deploy Script

In Forge: **Site → Deploy Script**, replace the contents with:

```bash
# Create the shared SQLite file if this is the first deploy
if [ ! -f "$FORGE_SITE_ROOT/database/database.sqlite" ]; then
    touch "$FORGE_SITE_ROOT/database/database.sqlite"
fi

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

$FORGE_PHP composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build

$FORGE_PHP artisan blogwriter:install --non-interactive \
  --site-name="Your Blog Name" \
  --site-url="https://yourdomain.com" \
  --admin-name="Your Name" \
  --admin-email="you@example.com" \
  --admin-password="CHANGE_ME_STRONG_PASSWORD"

$FORGE_PHP artisan storage:link
$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache

$ACTIVATE_RELEASE()

$RESTART_QUEUES()
```

<x-callout type="info" title="What This Script Does">
  The script runs in three phases:

  1. **Before `$CREATE_RELEASE()`** — Creates the shared SQLite file if it doesn't exist yet (first deploy only). This runs in the site root, not the release directory.
  2. **Between `$CREATE_RELEASE()` and `$ACTIVATE_RELEASE()`** — Installs dependencies, builds frontend assets, runs the installer, and warms caches. This all happens in the new release directory before it goes live.
  3. **After `$ACTIVATE_RELEASE()`** — The new release is now live. Forge restarts queue workers (a no-op for sync queues, but safe to include).
</x-callout>

<x-callout type="info" title="First Deploy vs. Subsequent Deploys">
  The <code>blogwriter:install</code> command is idempotent. On the first deploy it sets up your database, creates your admin account, and writes <code>storage/installed.lock</code>. On every subsequent deploy, it detects the lock file and exits immediately — no reinstallation, no data loss.
</x-callout>

<x-callout type="warning" title="Admin Password in the Deploy Script">
  Your deploy script is visible to anyone with Forge access to your server. Use a strong, unique password. It's safe to leave in the script after the first deploy — subsequent runs are a no-op — but you can also remove the <code>--admin-password</code> flag after your site is live.
</x-callout>

---

## Step 7 — Obtain an SSL Certificate

In Forge: **Site → SSL → Let's Encrypt → Obtain Certificate**.

Wait for DNS to propagate before doing this — the cert request will fail if your domain isn't resolving yet. Check propagation at [dnschecker.org](https://dnschecker.org).

<x-callout type="info" title="Cloudflare Users">
  Wait for the Let's Encrypt cert to be issued successfully, then re-enable orange-cloud proxying in Cloudflare if you want it.
</x-callout>

---

## Step 8 — Deploy

Click **Deploy Now** in Forge. The first deploy is slower than usual — Composer installs all dependencies and npm builds your frontend assets from scratch. Watch the deploy log in real-time to confirm everything completes.

---

## Verify Your Deployment

Once the deploy finishes:

1. **Visit your domain** — the BlogWriter home page loads
2. **Log in** at `/admin` with the credentials from your deploy script
3. **Run a diagnostic** via Forge's **Commands** panel: `php artisan blogwriter:diagnose`
4. **Upload a photo** — confirm it persists across a subsequent deploy (the real test of your shared paths config)

---

## Subsequent Deploys

Push changes to GitHub. Forge can auto-deploy on push (enable in Site → Deployments), or you can click **Deploy Now** manually.

Each deploy installs updated dependencies, rebuilds assets, refreshes caches, and activates the new release. The install command is a no-op after the first run. Your database and uploaded media are untouched.

---

## Troubleshooting

**Empty site or 500 error** — Check the deploy log in Forge for the error. Most first-deploy issues are in the Composer or npm steps.

**"Cannot find SQLite file"** — Your shared path for `database/database.sqlite` isn't configured or the file doesn't exist yet. Re-check Step 4 and confirm the file was created before the first deploy.

**Media not persisting across deploys** — The `storage` shared path isn't configured. Re-check Step 4. Note: you may need to recreate the site if zero-downtime deployments weren't enabled at creation.

**SSL certificate request failed** — DNS hasn't propagated yet. Check [dnschecker.org](https://dnschecker.org) and retry once your domain resolves.

**npm errors during deploy** — Confirm Node 18+ is installed. Run `node -v` in the Forge **Commands** panel.

**Forgot your admin password** — Run `php artisan blogwriter:user:reset-password` in the Forge Commands panel.

**General issues** — Run `php artisan blogwriter:diagnose` in the Forge Commands panel for a full health check.

---

#### [Up Next: *Writing Content*](writing-content.md)
