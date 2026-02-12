---
title: Settings
description: Configure your BlogWriter site, author information, themes, email, and advanced options.
extends: _layouts.documentation
section: content
category: configuration
order: 6
---

# Settings

Configure your BlogWriter site from the Settings page in your admin dashboard. All settings can be updated through the web interface, or if you prefer, via configuration files or the command line.

## Site Settings

### Site Name

The name of your blog. Appears in:
- Header/navigation
- Page titles (`<title>`)
- RSS/Atom/JSON feeds
- Open Graph metadata

**Example:** "My Blog" or "Jane Smith's Writing"

### Tagline

A short description of your blog (1-2 sentences).

**Where it appears:**
- Below your site name in some themes
- Site metadata
- Feed descriptions

**Example:** "Thoughts on technology, writing, and life"

### Domain

Your site's full domain including protocol.

**Format:** `https://yourdomain.com`

**Used for:**
- Generating canonical URLs
- Feed URLs
- IndieAuth
- Microformats markup

<x-callout type="warning" title="Important">
  Make sure this matches your actual domain. It's critical for feeds and IndieWeb features to work correctly.
</x-callout>

---

## Author Settings

### Name

Your display name.

**Where it appears:**
- Article bylines
- Author bio sections
- h-card (your IndieWeb identity)
- Feed author information

### Bio

A short about-you description (1-3 sentences).

**Where it appears:**
- Author bio cards on articles
- h-card on your homepage
- Theme footer (if your theme includes it)

**Example:** "Developer and writer based in Portland. Building tools for the open web."

### Avatar

Your profile photo.

**Upload:**
- Click "Upload Avatar" in Settings
- Drag and drop or browse for an image
- Square images work best (minimum 200×200px)

**Where it appears:**
- Author bio sections
- h-card
- Some themes display it in headers or footers

### Email

Your contact email address.

**Privacy:** Not displayed publicly by default. Used for:
- Admin notifications
- h-card metadata (hidden from visual display)
- Contact forms (if your theme includes one)

You can choose to display it publicly in your theme if you want.

---

## Content Settings

### Markdown Export

Enable or disable `.md` URLs for articles and notes.

**When enabled:**
- Visitors can append `.md` to any article or note URL
- Returns raw Markdown with YAML frontmatter
- `Content-Type: text/markdown` header
- Useful for LLMs, automated tools, and content portability

**When disabled:**
- `.md` URLs return 404
- Content only available as HTML

**Default:** Enabled

**Example:**
- HTML: `yourdomain.com/blog/my-article`
- Markdown: `yourdomain.com/blog/my-article.md`

### Automatic Backups

BlogWriter automatically backs up all articles as Markdown files with YAML frontmatter. This setting controls where backups are stored.

**Options:**
- **Local disk** (default) — Saved to `storage/backups/`
- **Cloud storage** — Sync to S3, Dropbox, or other services

**What's backed up:**
- Article content as Markdown
- Frontmatter (title, date, categories, tags, slug)
- Not backed up: drafts, notes, photos (stored in database only)

---

## Theme Settings

### Active Theme

Choose which theme your site uses. Changes take effect immediately.

**Included themes:**
- **Terminal** — Default theme with retro terminal aesthetic
- **Starter** — Minimalist foundation for building custom themes

**Installing themes:**
1. Click "Upload Theme"
2. Drag and drop a `.zip` file
3. Theme appears in the list immediately

### Theme Options

Some themes include customization options that appear here when activated:

- Color schemes
- Layout preferences
- Typography choices
- Feature toggles (table of contents, reading progress bar, etc.)

Options are theme-specific. Not all themes have customization options.

---

## Feed Settings

### Items Per Feed

Number of posts to include in RSS/Atom/JSON feeds.

**Default:** 50

**Recommendation:** 25-50 posts is typical. More than 100 can slow down feed readers.

### Feed Description

Optional custom description for feeds. If blank, uses your site tagline.

---

## IndieWeb Settings

### IndieAuth

Enable or disable your built-in IndieAuth server.

**When enabled:**
- You can sign in to other IndieWeb services using your domain
- Your site advertises authorization and token endpoints
- OAuth-compatible authentication

**When disabled:**
- IndieAuth endpoints return 404
- You can't use your domain to sign in elsewhere

**Default:** Enabled

### Webmention

Enable or disable webmention support.

**When enabled:**
- Your site can receive webmentions from other sites
- Advertises webmention endpoint in page headers
- Displays received mentions on posts (if theme supports it)

**When disabled:**
- Webmention endpoint returns 404
- No mention notifications

**Default:** Disabled (coming soon)

---

## Email Settings

Configure outgoing email for contact forms, password resets, and system notifications.

### Quick Start (Recommended Path)

**For most people, this is the easiest setup:**

1. Sign up for [Resend](https://resend.com) (free tier: 3,000 emails/month)
2. Get your API key from the Resend dashboard
3. In BlogWriter Settings → Email, select "Resend" and paste your API key
4. Add the DNS records Resend provides to your domain registrar
5. Send a test email to verify it works

**Total setup time:** 10-15 minutes including DNS propagation.

**Alternative:** [Postmark](https://postmarkapp.com) if you want premium deliverability and don't mind paying $15/month after the trial.

### Mail Driver

Choose how your site sends email:

**Resend** — **Recommended.** Modern email API with 3,000 emails/month free. Simple setup, great deliverability.

**Postmark** — **Also recommended.** Premium deliverability. 100 emails/month free trial, then $15/month for 10,000 emails.

**Mailgun** — Popular API service. 5,000 emails/month free. Good option if you're already familiar with it.

**Amazon SES** — Cheapest at scale ($0.10 per 1,000 emails), but requires AWS account and more complex setup.

**SMTP** — Use your existing email provider (Gmail, Fastmail, your web host). Works but can have deliverability issues.

**Log** — Development only. Writes emails to logs instead of sending them.

**Default:** Log (safe for development)

<x-callout type="info" title="Recommendation">
  Use Resend or Postmark. They're easier to set up than SMTP and have better deliverability. Save SMTP for when you already have email infrastructure set up.
</x-callout>

### SMTP Configuration

If using SMTP, you'll need these settings from your email provider:

**Host:** Your mail server address
**Port:** Usually `587` (TLS) or `465` (SSL)
**Username:** Your email address or account username
**Password:** Your email password or app-specific password
**Encryption:** TLS (recommended) or SSL

**Common providers:**

**Gmail:**
- Host: `smtp.gmail.com`
- Port: `587`
- Encryption: TLS
- [Create app password](https://support.google.com/accounts/answer/185833) instead of using your regular Gmail password

**Fastmail:**
- Host: `smtp.fastmail.com`
- Port: `465`
- Encryption: SSL
- Use your regular password

**Your web host:**
- Check your hosting control panel for SMTP settings
- Often: `mail.yourdomain.com` or `smtp.yourdomain.com`

### API-Based Services

For Mailgun, Postmark, Resend, or Amazon SES, you'll need an API key from that service. BlogWriter walks you through setup when you select one of these options.

**Why use an API service instead of SMTP?**

- Better deliverability (less likely to land in spam)
- Detailed sending statistics
- Webhooks for bounces and complaints
- Higher sending limits

**When to switch from SMTP to an API service:**

- You're sending more than 50 emails per day
- Your contact form emails land in spam
- You want sending analytics
- You're launching a newsletter (coming soon)

### From Address

**From Name:** The name that appears in recipients' inboxes
**From Email:** The email address messages come from

**Example:**
- From Name: `Your Blog Name`
- From Email: `noreply@yourdomain.com`

**Best practice:** Use an email address on your own domain (like `noreply@yourdomain.com`) rather than `yourname@gmail.com`. This improves deliverability and looks more professional.

**Setting up email on your domain:**

Most web hosts let you create email addresses for your domain in their control panel. You don't need to use these addresses day-to-day — just create one for BlogWriter to send from.

### DNS Records (Required for Production)

**No matter which email service you use**, you need to add DNS records to your domain. This is how receiving email servers know your emails are legitimate and not spam.

**Required records:**

**SPF (Sender Policy Framework)**
- Authorizes which mail servers can send email on behalf of your domain
- Format: `v=spf1 include:mailserver.com ~all`
- Added as a TXT record on your root domain

**DKIM (DomainKeys Identified Mail)**
- Cryptographic signature that proves your emails weren't tampered with
- Your email service generates a public/private key pair
- You add the public key as a TXT record (usually on a subdomain like `default._domainkey`)

**DMARC (Domain-based Message Authentication)**
- Tells receiving servers what to do if SPF/DKIM checks fail
- Start with monitoring: `v=DMARC1; p=none; rua=mailto:you@yourdomain.com`
- Later, tighten to `p=quarantine` or `p=reject` once you're confident

**Where to add DNS records:**

Log into your domain registrar (Namecheap, Cloudflare, Google Domains, etc.) and add these records in the DNS settings panel.

**How this works with different services:**

**Using SMTP (Gmail, Fastmail, your web host):**
- If using Gmail/Fastmail with your custom domain, they provide DNS records to add
- If using your web host's mail server, they may have already configured SPF/DKIM for you
- Check your provider's documentation for the exact records

**Using API services (Mailgun, Postmark, Resend, Amazon SES):**
- After entering your API key in BlogWriter, the service provides the exact DNS records to add
- You copy/paste these records into your domain's DNS settings
- The service verifies you added them correctly before allowing you to send
- BlogWriter shows you these records and their verification status

**Example DNS setup for Mailgun:**
1. You enter your Mailgun API key in BlogWriter
2. Mailgun provides 2-3 DNS records (SPF, DKIM, tracking domain)
3. You add these to your domain's DNS settings
4. Mailgun verifies them (usually takes 5-15 minutes)
5. You can start sending

<x-callout type="warning" title="DNS is not optional">
  Without SPF and DKIM records, your emails will land in spam or be rejected entirely. Every legitimate email service requires this setup.
</x-callout>

### Email Logs

**When using Log driver** (development mode), BlogWriter shows sent emails directly in the admin panel:

**Settings → Email → View Email Logs**

This shows emails in a readable format without needing to SSH into your server or read raw log files. Perfect for testing contact forms and other email features during development.

**In production**, email logs show:
- Successfully sent emails
- Failed delivery attempts
- Bounce notifications (if your email service supports webhooks)

### Testing Email

After configuring email:

1. Click "Send Test Email"
2. Enter your email address
3. Check your inbox (and spam folder)

**If the test fails:**
- Double-check your SMTP credentials
- Make sure your server allows outbound connections on port 587 or 465
- Verify DNS records are set up correctly (use [MXToolbox](https://mxtoolbox.com/))
- Try a different SMTP port (587 vs 465)

**If test emails land in spam:**
- Check your SPF/DKIM records at [MXToolbox](https://mxtoolbox.com/SuperTool.aspx)
- Make sure you're sending from your domain, not a third-party email address
- Consider switching to an API-based service (Mailgun, Postmark, Resend)

**Still not working?** Switch to Log driver and check the email logs in your admin panel to see the full error message.

---

## Advanced Settings

### Timezone

Set your site's timezone for publishing schedules and timestamps.

**Default:** Detected from server

### Date Format

Choose how dates are displayed throughout your site.

**Options:**
- `F j, Y` — January 31, 2026
- `M j, Y` — Jan 31, 2026
- `Y-m-d` — 2026-01-31
- Custom format

### Permalink Structure

**Articles:** `/blog/[slug]`
**Notes:** `/notes/[uuid]`
**Photos:** `/photos/[id]`

> **Note:** Permalink structure cannot be changed after content is published. Permanent links matter.

---

## Developer Settings

For developers who prefer working with configuration files:

**Config file:** `config/blogwriter.php`

**Environment variables:** `.env`

**CLI commands:**
```bash
# View current settings
php artisan config:show blogwriter

# Update a setting
php artisan config:set blogwriter.site.name "New Site Name"

# Clear config cache
php artisan config:clear
```

All settings in the web interface update the same underlying configuration. Use whichever method you prefer.

---

## Backup & Export

### Export All Settings

Download your complete site configuration as JSON. Useful for:
- Migrating to a new server
- Version control
- Backup before major changes

**Export includes:**
- All settings from this page
- Theme configuration
- Content statistics
- Does NOT include: posts, media files, database

### Import Settings

Upload a previously exported JSON file to restore settings.

<x-callout type="warning" title="Warning">
  This overwrites your current settings. Make sure you have a backup first.
</x-callout>

---

## Tips

- **Test changes on staging first** — If you have a staging environment, test major setting changes there before applying to production
- **Permalink changes break links** — Never change permalink structure after publishing content
- **Domain changes affect feeds** — If you change your domain, old feed subscribers may need to resubscribe
- **Check validators** — After changing IndieWeb settings, run your site through [IndieWebify.me](https://indiewebify.me) to verify everything still works

#### [Up Next: *Feeds & IndieWeb*](/docs/configuration/feeds-and-indieweb)
