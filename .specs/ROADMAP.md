# BlogWriter Development Roadmap

**Vision:** "Remember when *we* owned the internet?"

An IndieWeb-native blogging platform that makes owning your content as easy as using Substack—but without the platform
fees or platform risk.

---

## Core Philosophy

- **"Apple easy" self-hosting** - One command install, works out of the box
- **FOSS forever** (MIT License) - Use however you want
- **Open plugin architecture** - Composer-based plugins with official + third-party stores
- **Documentation from day 1** - Make onboarding seamless
- **IndieWeb-native** - Protocols built in, not bolted on

---

## V0.1 - IndieWeb Foundation

**Goal:** Ship a working IndieWeb blog you actually use.

**Success Metric:** Your personal site runs on BlogWriter and validates on IndieWeb validators.

### Identity & Authentication

- [x] Personal domain setup
- [x] Laravel Fortify authentication (custom UI with Blade + Alpine.js)
- [ ] IndieAuth server implementation (domain = identity)
- [x] h-card markup (homepage identity)
- [x] rel-me links (identity elsewhere)

### Content Types

- [x] Articles (long-form with titles)
- [ ] Notes (short Twitter-style posts)
- [ ] Pages (static content: About, Contact, etc.)
- [ ] Markdown auto-backup system
- [x] Permalinks for all content

### Frontend

- [x] Alpine.js + Alpine AJAX integration
- [x] DaisyUI default theme
- [x] Microformats markup (h-entry, h-card, h-feed)
- [x] Responsive design
- [x] Dark mode support

### Publishing

- [x] Article editor (Tiptap WYSIWYG editor)
- [ ] Note composer (quick post interface)
- [x] Draft/published workflow
- [x] Categories (tags planned)
- [x] Featured images
- [ ] SEO optimization

### Feeds

- [x] RSS feed
- [x] Atom feed
- [x] JSON Feed
- [x] Feed discovery `<link>` tags

### Installation

- [x] One-command installer: `php artisan blogwriter:install`
- [x] Works on shared hosting ($3-5/month)
- [x] SQLite database setup

### Documentation

- [ ] Custom Filament CMS for docs
- [x] Getting started guide
- [ ] IndieAuth setup walkthrough
- [ ] First post tutorial
- [ ] Theming basics
- [x] Troubleshooting guide
- [ ] `.md` URL support (example.com/page.md)

### Core Features

- [ ] `.md` route support (LLM-friendly, portable)
- [x] Beautiful defaults
- [x] Simple admin interface

---

## V0.2 - Federation & Discovery

**Goal:** Connect to the wider IndieWeb.

**Success Metric:** Can have conversations across independent websites.

### Webmention

- [ ] Webmention endpoint (receive)
- [ ] Webmention sending (when linking)
- [ ] Webmention verification
- [ ] Webmention display (comments/interactions)
- [ ] Spam filtering

### Micropub

- [ ] Micropub endpoint
- [ ] Post from mobile apps (Indigenous, Quill)
- [ ] Media endpoint
- [ ] Update/delete support
- [ ] Query support

### Federation

- [ ] Reply contexts (show original post)
- [ ] POSSE to Bluesky
- [ ] POSSE to Mastodon
- [ ] Backfeed (pull interactions back)
- [ ] Syndication tracking

### Discovery

- [ ] WebSub support (real-time updates)
- [ ] IndieWeb profile discovery
- [ ] Social graph building

### Documentation

- [ ] IndieWeb protocols explained
- [ ] Webmention setup guide
- [ ] Micropub client recommendations
- [ ] POSSE configuration walkthroughs
- [ ] Federation troubleshooting

---

## V0.3 - Creator Monetization

**Goal:** Substack alternative without the 10% fee.

**Success Metric:** Someone can run a paid newsletter on their domain.

### Newsletter System

- [ ] Integrated newsletter management
- [ ] Subscriber management
- [ ] Email templates (Blade-based)
- [ ] "Send as newsletter" option on posts
- [ ] Scheduled sending
- [ ] Basic analytics (opens, clicks)

### Email Relay Integration (BYOK)

- [ ] SendGrid integration
- [ ] Mailgun integration
- [ ] Postmark integration
- [ ] Amazon SES integration
- [ ] Resend integration
- [ ] Generic SMTP support
- [ ] Email relay setup wizard

### Monetization

- [ ] Stripe integration (Cashier)
- [ ] Subscription tiers (free/paid/premium)
- [ ] Content gating (members-only posts)
- [ ] Member management
- [ ] Billing portal
- [ ] Webhook handling

### Documentation

- [ ] "How to set up SendGrid" (step-by-step)
- [ ] "How to set up Mailgun" (step-by-step)
- [ ] "How to set up Postmark" (step-by-step)
- [ ] "How to set up Amazon SES" (step-by-step)
- [ ] "Choosing an email provider" (comparison guide)
- [ ] Stripe setup walkthrough
- [ ] Newsletter best practices
- [ ] Growing your subscriber list

---

## Future

Features beyond V0.3 are planned but not assigned to rigid version numbers. They ship when ready.

### Rich Media

- [ ] Podcast feed (RSS with enclosures), episode management, show notes
- [ ] Video posts with player embed, thumbnails, chapters, transcripts
- [ ] BYOK media hosting (Bunny.net, Vimeo, S3-compatible)
- [ ] Media library with upload interface and bulk operations

### BlogWriter Hosted (SaaS)

A separate Laravel management application that provisions BlogWriter instances inside Docker containers. The management app makes artisan calls under the hood — the FOSS product requires CLI skills; the hosted version provides the GUI for non-technical users.

- [ ] Docker containerization (one container per customer)
- [ ] Customer provisioning, billing (Stripe), and monitoring
- [ ] $20/month or $199/year pricing
- [ ] Marketing site and onboarding

### Ecosystem

- [ ] Theme marketplace (free + premium)
- [ ] Plugin store (Composer-based, open API spec, 15% official commission)
- [ ] Import tools (Substack, Medium, WordPress, Ghost)

### Polish

- [ ] Comprehensive test suite and security audit
- [ ] Performance benchmarks and accessibility audit
- [ ] Multi-language support

---

## Future Vision (Post-V0.6)

### Social Reader App (Maybe)

- Unified feed reader (RSS, Bluesky, Mastodon, IndieWeb)
- Micropub posting client
- Works with ANY Micropub blog
- NativePHP Mobile implementation
- Portfolio piece

### The Clubhouse (Maybe)

- Self-hosted community platform
- Forum + Chat + Newsletter
- Invite-only quality control
- IndieAuth login
- SaaS revenue ($20-50/month per community)

**Note:** These are opportunities, not commitments. Build if V0.5 validates the business model.

---

## Technical Decisions

### Stack

- **Language:** PHP 8.4+
- **Framework:** Laravel 12
- **Database:** SQLite (simple, portable, fast)
- **Authentication:** Laravel Fortify
- **Frontend:** Blade + Alpine.js + Alpine AJAX + Tailwind CSS + DaisyUI
- **Editor:** Tiptap with tiptap-markdown extension (current), block-based rich text editor (planned)
- **Runtime:** FrankenPHP (optional, for performance)

### Why This Stack

- PHP + SQLite works on $3/month shared hosting
- Laravel = modern DX, great ecosystem
- No Node.js daemons = simpler deployment
- Markdown backups = always exportable
- DaisyUI = fast, accessible components

### Plugin Architecture

- Plugins are standard Composer packages with Laravel auto-discovery
- Official store (curated, 15% commission) + third-party stores (open spec, 0% commission)
- Install via UI store, direct Composer, or manual filesystem
- No lock-in: plugins work regardless of installation source
- See `.specs/plugins.md` for full architecture spec

---

## Business Model

### Revenue Streams

1. **BlogWriter Hosted** ($20/month) - Primary revenue
2. **Premium Themes** ($29-79) - Secondary revenue
3. **Consulting** (as needed) - Tertiary revenue
4. **Plugin Store** (15% commission on official store) - Ecosystem revenue

### What We Don't Do

- ❌ Agency tiers
- ❌ White-label fees
- ❌ Per-site pricing
- ✅ Plugin marketplace (open architecture, 15% official store commission)
- ❌ Certification programs
- ❌ Platform fees (10% like Substack)

### Philosophy

- Keep it simple
- Two products: self-hosted (free) and hosted ($20/month)
- Users bring own keys (email, CDN, Stripe)
- We provide software and (optionally) hosting
- They keep 100% of revenue (minus payment processing)

---

## Competitive Positioning

### vs. Substack

- ✅ No 10% platform fee
- ✅ Own your domain
- ✅ Podcast + video included
- ✅ IndieWeb federation
- ✅ Export anytime

### vs. Patreon

- ✅ No 5-12% platform fee
- ✅ Everything on your domain
- ✅ Newsletter included
- ✅ Better for long-form content
- ✅ Own your subscriber relationships

### vs. Ghost

- ✅ Cheaper ($3-20/month vs. $9-199/month)
- ✅ IndieWeb protocols native
- ✅ Video/podcast native
- ✅ PHP (easier/cheaper hosting)
- ✅ SQLite (simpler deployment)

### vs. WordPress

- ✅ Single-purpose (not bloated)
- ✅ Modern tech stack
- ✅ Creator-focused from day one
- ✅ IndieWeb native
- ✅ Curated plugin ecosystem (vs WordPress bloat)

**The Pitch:** "Substack + Patreon for people who own their web."

---

## Success Indicators

### V0.1

- [ ] Your personal site runs on BlogWriter
- [ ] IndieAuth validates correctly
- [ ] Microformats pass validators
- [ ] Others can install it successfully
- [ ] Documentation covers basics

### V0.2

- [ ] Webmentions work across sites
- [ ] Can post from mobile Micropub clients
- [ ] POSSE copies appear on silos
- [ ] Backfeed pulls interactions back

### V0.3

- [ ] Newsletter sends successfully
- [ ] Stripe charges work
- [ ] Content gating functions
- [ ] Someone runs a paid newsletter

### V0.4

- [ ] Podcast feed works in Apple Podcasts
- [ ] Video plays from Bunny.net CDN
- [ ] Creator publishes podcast on their domain
- [ ] Media costs stay low

### V0.5

- [ ] First paying hosted customer
- [ ] Provisioning is automated
- [ ] Billing works reliably
- [ ] Support is manageable
- [ ] Margins above 80%

---

## Principles to Remember

1. **IndieWeb-native from day one** - Not bolted on later
2. **Documentation is a feature** - Ship docs with code
3. **Simple beats complex** - One way to do things
4. **No artificial deadlines** - Ship when it works
5. **Your blog validates the product** - Use what you build
6. **BYOK keeps costs down** - Users provide API keys
7. **Open plugin ecosystem** - Composer packages, open store spec, no lock-in
8. **Ownership is the point** - Domain = identity, markdown = portability
9. **Build in public** - Share progress, get feedback
10. **Ship small, iterate fast** - V0.1 first, everything else follows

---

## Current Status

**Phase:** Pre-Alpha
**Next:** Complete remaining V0.1 features
**Focus:** Notes, Tags, Feeds, IndieAuth

---

**Last Updated:** February 2026
