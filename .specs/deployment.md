# BlogWriter Deployment Guide

First-time deployment to Laravel Forge with a production site and staging subdomain. The repo must be pushed to public GitHub first. The app uses SQLite, file-based sessions/cache, and sync queues — no Redis, MySQL, or workers required.

---

## Step 1: Push to GitHub

1. Create a new **public** repo on GitHub
2. Push your local repo:
   ```bash
   git remote add origin https://github.com/yourusername/your-repo.git
   git branch -M main
   git push -u origin main
   ```
3. Confirm the repo is visible at github.com

---

## Step 2: Forge Server

- Log in to [forge.laravel.com](https://forge.laravel.com)
- Confirm you have a provisioned server with **PHP 8.4** active
  - Check: Server → PHP → ensure 8.4 is installed and set as default
- Note your server's **IP address** — you'll need it for DNS

---

## Step 3: DNS (Do This Early — DNS Propagates Slowly)

In your DNS provider (Cloudflare or Hostinger), add:

| Type | Name | Value |
|------|------|-------|
| A | `yourdomain.com` | Forge server IP |
| A | `staging` | Forge server IP |

> **Cloudflare users:** Start with the orange cloud (proxy) **disabled** (DNS-only) until SSL is confirmed working. You can re-enable proxying after.

---

## Step 4: Create Production Site in Forge

1. Forge → your server → **Sites** → **New Site**
2. Domain: `yourdomain.com`
3. Web root: `/public`
4. Project type: Laravel
5. Connect GitHub repo (Forge will ask to link your GitHub account on first use — public repo, no deploy key needed)
6. Branch: `main`

---

## Step 5: Create Staging Site in Forge

Same server → **New Site**:
1. Domain: `staging.yourdomain.com`
2. Web root: `/public`
3. Same GitHub repo, branch: `main`

---

## Step 6: SSL Certificates

For each site, after DNS propagates:
- Site → **SSL** → "Obtain Let's Encrypt Certificate"

> This will fail if DNS hasn't propagated yet. You can check propagation at [dnschecker.org](https://dnschecker.org).

---

## Step 7: Environment Variables

In Forge, for each site → **Environment** tab, set:

**Production:**
```ini
APP_NAME="Your Blog Name"
APP_ENV=production
APP_KEY=                          # leave blank, install command generates it
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=error
```

**Staging** (same, but adjust these):
```ini
APP_NAME="Your Blog Name (Staging)"
APP_ENV=production
APP_URL=https://staging.yourdomain.com
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## Step 8: Deploy Script

In Forge → site → **Deploy Script** tab, set for **each site** (adjust URLs/credentials per site):

```bash
cd /home/forge/yourdomain.com
git pull origin main

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
```

> **Note:** The install command creates `storage/installed.lock` after the first successful run. On future deploys it skips reinstallation automatically. The caching commands are safe to re-run on every deploy.

---

## Step 9: Deploy

1. Forge → site → **Deploy** → "Deploy Now"
2. Watch the deploy log in real-time
3. If it errors: check the log — common issues are missing Node/npm or PHP version mismatch

---

## Step 10: Verify

After deploy:

1. Visit `https://yourdomain.com` — should load BlogWriter
2. Log in with the admin credentials set in the deploy script
3. Run a diagnostic via Forge's **Commands** tab:
   ```bash
   php artisan blogwriter:diagnose
   ```
4. Repeat for `https://staging.yourdomain.com`

---

## Important Notes

- **Node on Forge:** Forge servers have Node installed, but confirm the version (`node -v` in the Forge commands panel). Vite requires Node 18+.
- **SQLite file location:** Lives at `{site_root}/database/database.sqlite` — created by the install command. Back this file up periodically.
- **Staging database is separate** from production (different site root = different SQLite file). They share no data.
- **Future deploys:** Push to GitHub and click "Deploy Now" in Forge (or enable auto-deploy on push in the Forge site settings).