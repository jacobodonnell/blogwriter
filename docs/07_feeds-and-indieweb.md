# Feeds & IndieWeb

**The web was supposed to be decentralized.**

Before Facebook, before Twitter, before Substack — we had RSS feeds, blogs that linked to each other, and protocols
anyone could use. You owned your words. You controlled your distribution. The web was a network of independent voices,
not a handful of platforms deciding what gets seen.

BlogWriter brings that back. Not out of nostalgia, but because it's the right architecture for publishing on the
internet.

This page explains what BlogWriter does to make you a citizen of the open web — feeds, microformats, IndieAuth. None of
it requires configuration. It just works.

---

## Feeds: You Publish, They Subscribe

Feeds are how the decentralized web works. You publish to your own site. Readers subscribe with the tool of their
choice. No algorithm in between deciding what to show. No platform taking a cut. Just a simple protocol that's worked
since 1999.

### What BlogWriter Generates

Your blog automatically produces three feed formats:
    
| Feed      | URL                                 | Format        |
|-----------|-------------------------------------|---------------|
| RSS       | `yourdomain.com/feed` (also `/rss`) | RSS 2.0       |
| Atom      | `yourdomain.com/atom`               | Atom 1.0      |
| JSON Feed | `yourdomain.com/feed.json`          | JSON Feed 1.1 |

All three include your last 50 posts — articles, notes, photos — with full content. Newest first.

### Why Multiple Formats?

Different tools prefer different formats. RSS is the most widely supported. Atom is more precise. JSON Feed is easier
for developers. We generate all three so your content works everywhere.

### Who Uses Feeds?

Anyone with a feed reader: Feedly, NetNewsWire, Inoreader, Miniflux, or any of dozens of others. When you publish, your
subscribers see it. No one needs permission from a platform. No one can take your distribution away.

IndieWeb tools also consume feeds — Microsub readers, Bridgy for syndication, aggregators. The web talks to itself
through feeds.

### Discovery

BlogWriter tells browsers and feed readers where to find your feeds:

```html

<link rel="alternate" type="application/rss+xml"
      title="Your Site Name - RSS" href="https://yourdomain.com/feed"/>
<link rel="alternate" type="application/atom+xml"
      title="Your Site Name - Atom" href="https://yourdomain.com/atom"/>
<link rel="alternate" type="application/feed+json"
      title="Your Site Name - JSON Feed" href="https://yourdomain.com/feed.json"/>
```

Someone types your domain into their feed reader, it finds the feeds automatically. You don't have to tell anyone the
exact URL.

---

## Microformats: Structured Data for Humans

Microformats solve a problem: how do you mark up content so both humans and machines can understand it?

The answer: use classes that mean something. Not `.card` or `.post`, but `.h-entry` (this is a blog post) and
`.p-author` (this is the author). Simple, semantic, human-readable.

Microformats let other websites understand your content without needing an API or a partnership deal. Someone can write
software that reads your blog and knows what's a post, what's a title, when it was published — just by reading the HTML.

### Why This Matters

When you use microformats:

- IndieWeb services can read and display your posts
- Your blog becomes queryable by tools you haven't even heard of yet
- Other sites can properly attribute quotes and mentions
- Your identity travels with your content

You're not locked into one platform's data format. You're using standards.

### h-card: Your Identity

An h-card says "this is me." It's a virtual business card embedded in your homepage.

```html

<div class="h-card">
    <img src="/avatar.jpg" alt="Your Name" class="u-photo">
    <a href="https://yourdomain.com" class="u-url p-name" rel="me">Your Name</a>
    <p class="p-note">Writer, developer, human.</p>
</div>
```

When you sign in to an IndieWeb service, it checks your h-card. When someone mentions you, they use your h-card to
display your name and photo. You become portable. Your identity isn't locked in Facebook's database — it's on your
website, under your control.

### h-entry: Your Posts

Every post gets wrapped in h-entry markup. This tells machines what they're looking at:

- **Articles** have a title (`p-name`) and content (`e-content`)
- **Notes** have content but no title — the content *is* the name
- **Photos** have an image (`u-photo`) and optional caption

Example (article):

```html

<article class="h-entry">
    <h1 class="p-name">My Article Title</h1>
    <time class="dt-published" datetime="2026-01-31">January 31, 2026</time>
    <div class="e-content">
        <p>Article content goes here...</p>
    </div>
</article>
```

IndieWeb tools can now understand what this is, when it was published, and what the content says. No API needed. Just
HTML.

### h-feed: Your Post List

Wrap your homepage post list in an h-feed, and feed readers know it's a list of posts:

```html

<div class="h-feed">
    <span class="p-name hidden">My Blog</span>

    <article class="h-entry"><!-- post 1 --></article>
    <article class="h-entry"><!-- post 2 --></article>
    <article class="h-entry"><!-- post 3 --></article>
</div>
```

Same idea as RSS, but embedded directly in your HTML. Some tools prefer to read the HTML rather than fetching a separate
feed file. You support both.

---

## IndieAuth: Sign In With Your Domain

Why should Google or Facebook be your identity on the web? You have a domain. That's your identity.

IndieAuth lets you sign in to websites by proving you control your domain. No third-party identity provider needed.

### How It Works

1. You go to an IndieWeb service
2. They ask "Who are you?"
3. You give them your domain: `yourdomain.com`
4. They redirect you to your BlogWriter login
5. You log in and approve
6. They get a token proving you own that domain
7. You're signed in as `yourdomain.com`

Your domain is your username. Your BlogWriter installation is your identity provider.

### Why This Matters

You're not renting your identity from a corporation. You own it. If Facebook bans your account, you're still you — your
domain still works. If Twitter changes its name to X and implodes, you're unaffected.

Your identity on the web should be something you control. IndieAuth makes that real.

### Your Endpoints

BlogWriter provides these endpoints automatically:

| Endpoint      | URL                                                     | Purpose                              |
|---------------|---------------------------------------------------------|--------------------------------------|
| Authorization | `yourdomain.com/indieauth/auth`                         | Where users authorize apps           |
| Token         | `yourdomain.com/indieauth/token`                        | Where apps exchange codes for tokens |
| Metadata      | `yourdomain.com/.well-known/oauth-authorization-server` | OAuth server info                    |

You don't configure anything. It's just there, working.

### Testing It

Go to [IndieLogin.com](https://indielogin.com) and try signing in with your domain. If it works, congratulations —
you're using your own website as your identity.

---

## Why Any of This Matters

You could ignore all of this. Publish to Medium, build an audience on Twitter, use Substack for paid content. Millions
of people do.

But here's what you're giving up:

**Portability** — Your content is locked in their format, on their servers. Moving it is hard. Sometimes impossible.

**Control** — They can change the rules. Add paywalls. Alter the algorithm. Ban your account. You have no recourse.

**Longevity** — Platforms shut down. GeoCities, Google Reader, Vine, Tumblr (effectively), dozens more. Your content
disappears with them.

**Independence** — You're building on rented land. They own the relationship with your readers. They own your
distribution. You're a tenant, not a homeowner.

Feeds and microformats solve these problems. They're old, boring, and reliable. They work because they're simple. They
last because they're standards, not platforms.

BlogWriter implements them because we believe the web works better when people own their content, control their
distribution, and build on protocols instead of platforms.

---

## Validation

Want to check that your site implements everything correctly?

### IndieWebify.me

Visit [IndieWebify.me](https://indiewebify.me) and enter your domain. It checks:

- h-card (your identity)
- h-entry (your posts)
- Feed discovery

If you're using BlogWriter's built-in themes or components, you'll pass.

### Microformats Parsers

See what machines see:

- [php-mf2 parser](https://php.microformats.io)
- [pin13.net parser](https://pin13.net/mf2/)

Paste your URL, see the structured data.

### Feed Validators

Make sure your feeds are valid:

- [W3C Feed Validator](https://validator.w3.org/feed/) (RSS/Atom)
- [JSON Feed Validator](https://validator.jsonfeed.org/)

---

## Summary

BlogWriter gives you:

| Feature                                | What It Is                  | Why It Matters                                              |
|----------------------------------------|-----------------------------|-------------------------------------------------------------|
| RSS/Atom/JSON Feeds                    | Syndication formats         | Readers can subscribe without giving a platform their email |
| Microformats (h-card, h-entry, h-feed) | Semantic HTML markup        | Machines can understand your content without an API         |
| IndieAuth                              | Domain-based authentication | Your website is your identity                               |

None of this is theoretical. It's all implemented, tested, and working. Your blog speaks the protocols of the open web.

You're not dependent on platforms. You're a peer.

#### [Up Next: *Architecture*](08_architecture.md)
