# BlogWriter Development Roadmap

**Vision:** "Remember when *we* owned the internet?"

An IndieWeb-native blogging platform that makes owning your content as easy as using Substack—but without the platform
fees or platform risk.

---

## Core Philosophy

- **"Apple easy" self-hosting** - One command install, works out of the box
- **FOSS forever** (MIT License) - Use however you want
- **No plugins, ever** - Themes handle customization
- **Documentation from day 1** - Make onboarding seamless
- **IndieWeb-native** - Protocols built in, not bolted on

---

## V0.1 - IndieWeb Foundation

**Goal:** Ship a working IndieWeb blog you actually use.

**Success Metric:** Your personal site runs on BlogWriter and validates on IndieWeb validators.

### Identity & Authentication

- [ ] Personal domain setup
- [ ] Laravel Fortify authentication (custom UI with Blade + Alpine.js)
- [ ] IndieAuth server implementation (domain = identity)
- [ ] h-card markup (homepage identity)
- [ ] rel-me links (identity elsewhere)

### Content Types

- [ ] Articles (long-form with titles)
- [ ] Notes (short Twitter-style posts)
- [ ] Pages (static content: About, Contact, etc.)
- [ ] Markdown auto-backup system
- [ ] Permalinks for all content

### Frontend

- [ ] Alpine.js + Alpine AJAX integration
- [ ] DaisyUI default theme
- [ ] Microformats markup (h-entry, h-card, h-feed)
- [ ] Responsive design
- [ ] Dark mode support

### Publishing

- [ ] Article editor (Editor.js integration)
- [ ] Note composer (quick post interface)
- [ ] Draft/published workflow
- [ ] Categories and tags
- [ ] Featured images
- [ ] SEO optimization

### Feeds

- [ ] RSS feed
- [ ] Atom feed
- [ ] JSON Feed

### Installation

- [ ] One-command installer: `php artisan blogwriter:install`
- [ ] Installation wizard (web-based)
- [ ] Works on shared hosting ($3-5/month)
- [ ] SQLite database setup

### Documentation

- [ ] Custom Filament CMS for docs
- [ ] Getting started guide
- [ ] IndieAuth setup walkthrough
- [ ] First post tutorial
- [ ] Theming basics
- [ ] Troubleshooting guide
- [ ] `.md` URL support (example.com/page.md)

### Core Features

- [ ] `.md` route support (LLM-friendly, portable)
- [ ] Beautiful defaults
- [ ] Simple admin interface

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

## V0.4 - Rich Media (Patreon Alternative)

**Goal:** Full creator platform for podcasts and video.

**Success Metric:** Podcast/video creator owns their content on their domain.

### Podcasting

- [ ] Podcast feed (RSS with enclosures)
- [ ] Episode management
- [ ] Show notes (articles with audio)
- [ ] Podcast metadata (title, description, artwork)
- [ ] Submit to Apple Podcasts
- [ ] Submit to Spotify
- [ ] Submit to other directories

### Video

- [ ] Video posts (articles with video)
- [ ] Video player embed
- [ ] Thumbnails
- [ ] Video chapters
- [ ] Transcripts
- [ ] Members-only videos

### Media Hosting (BYOK)

- [ ] Bunny.net integration (recommended)
- [ ] Bunny Stream integration
- [ ] Vimeo integration (alternative)
- [ ] S3-compatible integration (Cloudflare R2, Backblaze B2)
- [ ] Media upload workflow
- [ ] CDN auto-publishing
- [ ] Bandwidth tracking
- [ ] Cost dashboard

### Media Management

- [ ] Media library
- [ ] Upload interface
- [ ] File organization
- [ ] Metadata editing
- [ ] Bulk operations

### Documentation

- [ ] "Setting up Bunny.net" (recommended)
- [ ] "Why Bunny.net for indie creators" (cost breakdown)
- [ ] "Setting up Vimeo"
- [ ] "Setting up Cloudflare R2"
- [ ] "Setting up Backblaze B2"
- [ ] "Podcast setup guide (Apple/Spotify)"
- [ ] "Video hosting costs explained"
- [ ] "Publishing workflow: website first, YouTube later"
- [ ] "Podcast best practices"
- [ ] "Video best practices"

---

## V0.5 - BlogWriter Hosted (SaaS)

**Goal:** Launch managed hosting for non-technical users.

**Success Metric:** First paying customers, profitable margins (80%+).

### Infrastructure

- [ ] Docker containerization
- [ ] One container per customer
- [ ] Forge + VPS setup
- [ ] Customer provisioning automation
- [ ] Traefik routing (or similar)
- [ ] Automated SSL (Let's Encrypt)
- [ ] Daily backups
- [ ] Monitoring (uptime, resources)

### Customer Management

- [ ] Signup flow
- [ ] Billing (Stripe)
- [ ] Customer dashboard
- [ ] Instance provisioning (via Forge API)
- [ ] Support ticketing
- [ ] Email onboarding sequence

### Pricing

- [ ] $20/month (monthly billing)
- [ ] $199/year (annual billing, save ~2 months)
- [ ] Pricing page
- [ ] Comparison: self-hosted vs. hosted

### Marketing Site

- [ ] Product overview
- [ ] Feature highlights
- [ ] Pricing page
- [ ] Testimonials
- [ ] Demo video
- [ ] "Substack alternative" positioning

### Documentation

- [ ] "Self-hosted vs. Hosted comparison"
- [ ] "Getting started with Hosted"
- [ ] "Migrating from hosted to self-hosted"
- [ ] "Exporting your data"
- [ ] Support documentation

---

## V0.6+ - Maturity & Polish

**Goal:** Ecosystem growth, community contributions, sustainable business.

### Theme Marketplace

- [ ] Theme submission system
- [ ] Premium themes ($29-79)
- [ ] Community themes (free)
- [ ] Theme showcase
- [ ] Revenue sharing for theme developers
- [ ] Theme review process
- [ ] Theme documentation standards

### Import Tools

- [ ] Substack import
- [ ] Medium import
- [ ] WordPress import
- [ ] Patreon import
- [ ] Ghost import
- [ ] Generic RSS import

### Advanced Features

- [ ] Custom domain setup (easier)
- [ ] Analytics integration (privacy-focused)
- [ ] Advanced SEO tools
- [ ] Performance optimizations
- [ ] Multi-language support
- [ ] Accessibility improvements

### Community

- [ ] Showcase of BlogWriter sites
- [ ] Theme developer resources
- [ ] IndieWeb integration examples
- [ ] Community forum
- [ ] Case studies
- [ ] Success stories

### Polish

- [ ] Comprehensive test suite
- [ ] Performance benchmarks
- [ ] Security audit
- [ ] Accessibility audit
- [ ] UX improvements based on feedback

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
- **Editor:** Editor.js (with Markdown backups)
- **Runtime:** FrankenPHP (optional, for performance)

### Why This Stack

- PHP + SQLite works on $3/month shared hosting
- Laravel = modern DX, great ecosystem
- No Node.js daemons = simpler deployment
- Markdown backups = always exportable
- DaisyUI = fast, accessible components

### No Plugins Policy

- Themes handle all customization
- Themes can add functionality
- Uninstall theme = clean slate
- Keeps core simple and maintainable
- WordPress bloat is the enemy

---

## Business Model

### Revenue Streams

1. **BlogWriter Hosted** ($20/month) - Primary revenue
2. **Premium Themes** ($29-79) - Secondary revenue
3. **Consulting** (as needed) - Tertiary revenue

### What We Don't Do

- ❌ Agency tiers
- ❌ White-label fees
- ❌ Per-site pricing
- ❌ Plugin marketplace
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
- ✅ No plugins = simpler

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
7. **No plugins ever** - Themes handle customization
8. **Ownership is the point** - Domain = identity, markdown = portability
9. **Build in public** - Share progress, get feedback
10. **Ship small, iterate fast** - V0.1 first, everything else follows

---

## Current Status

**Phase:** Planning
**Next:** Start building V0.1
**Focus:** Get basic blogging working with IndieWeb protocols

---

**Last Updated:** January 2026
