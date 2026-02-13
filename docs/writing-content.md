---
title: Writing Content
description: Learn how to create and manage articles, notes, and photos with BlogWriter's intuitive editors and publishing workflow.
extends: _layouts.documentation
section: content
category: content
category_order: 2
order: 3
---

# Writing Content

BlogWriter supports three types of content: **articles** (✅ implemented), **notes** (🚧 coming soon), and **photos** (✅ implemented). Each has its own editor and its own place on your site, sharing the same draft/publish workflow and appearing together in your feeds.

## The Admin Dashboard

After logging in at `/admin`, you'll see your dashboard:

- **Recent posts** across all types (articles, notes, photos)
- **Draft counts** and **published counts** for each type
- **Quick actions:** New Article, New Note, New Photo

The sidebar navigation gives you access to:

- **Articles** ✅
- **Notes** 🚧 (coming soon)
- **Photos** ✅
- **Categories** ✅
- **Tags** 🚧 (coming soon)
- **Settings** ✅ (minimal UI currently)
- **View Site** — opens your blog as visitors see it

---

## Articles ✅

Articles are long-form posts with titles. Think blog posts, essays, tutorials.

### The Editor

**Current:** Articles use a simple **Markdown textarea** editor. Write your content in Markdown and it will be rendered as HTML on your site.

**Why Markdown:**
- **Portable** — Standard format, works everywhere
- **Simple** — No complex editor dependencies
- **CLI-friendly** — Can write posts in any text editor
- **Version control** — Easy to diff and commit

> **🚧 Editor.js Coming Soon**
>
> A rich block-based editor (Editor.js) is planned as an alternative for those who prefer a visual editing experience. Markdown will remain supported even after Editor.js is added — important for CLI workflow. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues) on editor preferences.

**Planned Editor.js Features:**
- Build posts from individual blocks (paragraphs, headings, lists, quotes, code, images, embeds)
- Click between blocks to add new ones, drag to reorder
- Keyboard shortcuts and inline Markdown that converts to rich text (like Notion)
- Each block independent — no fighting with formatting

### Fields

- **Title** (required) — Your article's headline. BlogWriter auto-generates a URL slug from the title as you type. You can edit the slug manually.
- **Summary** (nullable) — A short description used in article lists and SEO meta tags.
- **Content** (required) — Markdown textarea for your article content.
- **Featured Photo** (nullable) — Select a photo to feature with the article.
- **Categories** ✅ — Assign one or more categories (e.g., "Tech", "Life", "Travel"). Create new categories on the fly.
- **Tags** 🚧 — Coming soon. Will work across all post types.
- **SEO fields** ✅ — Meta title, meta description, and Open Graph image.

### Draft and Publish

Every article starts as a **draft**. Drafts are only visible to you in the admin.

When you're ready, change the status to **Published**. BlogWriter sets the publish date to now (or you can schedule a
future date). Published articles appear on your site and in your feeds.

You can unpublish an article at any time by switching it back to draft.

### Where Articles Live

Your article's URL follows this structure:

```
yourdomain.com/articles/your-article-title
```

The slug is automatically generated from your title when you first create the article. Once published, the URL never changes — permanent links matter.

## The Customizer ✅

The Customizer is BlogWriter's article editor — a split-pane view with a live preview on the right and your editing controls on the left. As you type, your changes auto-save in the background and the preview updates instantly. No more guessing what your post looks like.

### Publishing Workflow

The save button adapts to what you're about to do:

- **Save Draft** — You're working on a draft. Save and keep writing.
- **Publish** — Your draft is ready. A confirmation dialog makes sure you meant it — once you publish, the article is live and visible to everyone.
- **Save Changes** — Already published? Edit freely. Your changes save without ceremony.
- **Unpublish** — Need to take something down? Switch the status to draft and confirm. The article disappears from your site (visitors see a 404) until you republish.
- **Republish** — Bringing back an unpublished article? BlogWriter preserves your original publish date so your content history stays intact.

### Featured Images

You can attach a featured image to any article in two ways:

1. **Select an existing Photo** from the dropdown
2. **Upload a new Photo** directly from the customizer — a modal handles the upload without leaving the editor, and the photo is automatically associated with your article

### Permalinks Are Forever

> *Cool URIs don't change.* — Tim Berners-Lee

When you change an article's slug, BlogWriter remembers the old one. Anyone visiting the old URL gets a **301 permanent redirect** to the new address. Search engines transfer their ranking, bookmarks keep working, and shared links don't break.

<!-- TODO: Add screenshots and video demos -->

> **🚧 Automatic Backups & Markdown Export Coming Soon**
>
> Automatic Markdown file backups and `.md` URL endpoints are planned but not yet implemented. Articles already store content as Markdown in the database. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues).

**Planned Features:**

BlogWriter will automatically back up each article as a Markdown file with YAML frontmatter. Your content will live in the database for fast retrieval, and as `.md` files for portability and peace of mind.

You'll be able to append `.md` to any article URL to get the raw Markdown version:

```
yourdomain.com/articles/your-article-title.md
```

This will return:

```markdown
---
title: Your Article Title
date: 2026-01-15
slug: your-article-title
categories: [Tech, Laravel]
tags: [php, blogging]
---

Your article content in Markdown...
```

**Current State:** Articles store Markdown in database `content` column. File export to be added.

---

> **🚧 Coming Soon: Notes**
>
> The Note content type is planned but not yet implemented. Model, controller, views, and routes need to be built. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues) on the Note design below.

## Notes (Planned)

Notes are short posts without titles. Use them for quick thoughts, links, updates — anything that doesn't need the full article treatment. Think tweets, but on your own site.

### The Editor

Notes use a streamlined inline editor (like Twitter or Facebook). Just start typing. You can use keyboard shortcuts or
type inline Markdown that automatically converts to rich text. The editor is simpler than the article editor — perfect
for quick posts.

You can paste images directly into notes, and they'll appear inline in your content.

### Fields

- **Content** (required) — Your note text. No title field — notes are titleless by design.
- **Tags** — Same tag system as articles. Create new tags on the fly.

### Publishing Notes

Notes don't have drafts — when you hit publish, they go live immediately. You can delete notes, but there's no
draft/publish workflow.

### Where Notes Live

Notes use UUIDs instead of slugs:

```
yourdomain.com/notes/a1b2c3d4-e5f6-7890-abcd-ef1234567890
```

### Markdown Export (Optional)

Like articles, you can append `.md` to get the raw Markdown version (if enabled in Settings):

```
yourdomain.com/notes/a1b2c3d4-e5f6-7890-abcd-ef1234567890.md
```

---

## Photos ✅

Photos are image posts with captions. For photographers, visual bloggers, or anyone who wants to share images as first-class content (not just inline in articles or notes).

**Implementation:** Uses [Spatie Laravel MediaLibrary](https://spatie.be/docs/laravel-medialibrary) for professional media handling with automatic image conversions.

### Uploading

Upload an image using the file picker. BlogWriter accepts common image formats (JPEG, PNG, WebP, GIF).

**MediaLibrary automatically generates multiple conversions:**
- **thumbnail** (150x150) — For gallery views
- **medium** (800x600) — For content display
- **large** (1600x1200) — For full-size viewing

### Fields

- **Image** (required) — The photo itself. Uploaded via Spatie MediaLibrary.
- **Title** (nullable) — Optional title for the photo.
- **Caption** (nullable) — Markdown text that appears with your photo.
- **Alt text** (nullable) — Describes the image for screen readers and when images can't load. Always fill this in.
- **External URL** (nullable) — Optional link to original source.
- **Tags** 🚧 — Coming soon. Will work same as articles.

<x-callout type="info" title="Technical Note">
  Spatie MediaLibrary handles EXIF metadata extraction, responsive image conversions, and file storage. Metadata can be accessed and displayed in your theme.
</x-callout>

### Draft and Publish

Same workflow. Draft until you're ready, then publish.

### Where Photos Live

```
yourdomain.com/photos/42
```

Photos use a numeric ID.

---

## Categories ✅

Categories organize your **articles** into broad topics. An article can belong to multiple categories.

Examples: "Tech", "Life", "Travel", "Cooking"

### Managing Categories

Go to **Categories** in the admin sidebar to manage all your categories. You can also create categories on the fly when
editing an article — just type a new category name and it'll open a popup to create it.

Each category gets a URL slug generated from its name:

```
yourdomain.com/category/tech
```

Visiting a category page shows all published articles in that category.

---

> **🚧 Coming Soon: Tags**
>
> Polymorphic tagging system is planned but not yet implemented. [Feedback welcome on GitHub](https://github.com/jacobodonnell/blogwriter/issues) on tagging design.

## Tags (Planned)

Tags will provide finer-grained organization and work across **all post types** — articles, notes, and photos.

Examples: "php", "laravel", "indieweb", "photography", "weekend"

### Managing Tags

Go to **Tags** in the admin sidebar to manage all your tags. You can also create tags on the fly when editing any post —
just type a new tag name and it's created automatically.

Each tag gets a page:

```
yourdomain.com/tag/laravel
```

Tag pages show all published posts (articles, notes, and photos) with that tag.

---

## Settings ⚠️ (Minimal UI Currently)

> **Current State:** Settings page exists but is minimal and read-only, displaying environment information only. Extensive settings UI is coming soon.

The Settings page will let you update your site and author information.

### Site Settings

- **Site name** — Appears in the header, feeds, and page titles
- **Tagline** — A short description of your blog
- **Domain** — Your site's domain (used for generating URLs and feeds)
- **Markdown export** — Enable or disable `.md` URLs for articles and notes

### Author Settings

- **Name** — Your display name, shown on posts and in your h-card
- **Bio** — A short about-you blurb
- **Avatar** — Your profile photo
- **Email** — Contact email (not displayed publicly by default)

### Theme

- **Active theme** — Choose which theme your site uses. Changes take effect immediately.

<x-callout type="info" title="For Developers">
  Settings can also be managed via the CLI or by editing configuration files directly.
</x-callout>

#### [Up Next: *Themes*](/docs/customization/themes)
