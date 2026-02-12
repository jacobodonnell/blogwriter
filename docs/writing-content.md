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

BlogWriter supports three types of content: articles, notes, and photos. Each has its own editor and its own place on
your site, but they all share the same draft/publish workflow and appear together in your feeds.

## The Admin Dashboard

After logging in at `/admin`, you'll see your dashboard:

- **Recent posts** across all types (articles, notes, photos)
- **Draft counts** and **published counts** for each type
- **Quick actions:** New Article, New Note, New Photo

The sidebar navigation gives you access to:

- Articles
- Notes
- Photos
- Categories
- Tags
- Settings
- **View Site** — opens your blog as visitors see it

---

## Articles

Articles are long-form posts with titles. Think blog posts, essays, tutorials.

### The Editor

Articles use a rich block editor where you build your post from individual blocks like paragraphs, headings, lists,
quotes, code blocks, images, and embeds. Click between blocks to add new ones. Drag to reorder. Each block is
independent — no fighting with formatting.

You can use keyboard shortcuts or type inline Markdown that automatically converts to rich text (like Notion). Start a
line with `#` for a heading, `-` for a list, or `>` for a quote.

<x-callout type="info" title="For Developers">
  BlogWriter uses Editor.js for the article editor, providing a modern block-based editing
  experience with full keyboard shortcut support.
</x-callout>

### Fields

- **Title** (required) — Your article's headline. BlogWriter auto-generates a URL slug from the title as you type. You
  can edit the slug manually.
- **Summary** — A short description used in article lists, feeds, and SEO meta tags. If you leave it blank, BlogWriter
  uses the first paragraph.
- **Content** — The rich block editor described above.
- **Categories** — Assign one or more categories (e.g., "Tech", "Life", "Travel"). Don't see the category you need? Type
  a new one and it'll open a popup to create it on the fly.
- **Tags** — Add tags for finer-grained organization. Tags work across all post types. Like categories, you can create
  new tags on the fly.
- **Photos** — Attach photos to your article. They can be displayed inline or in a gallery.
- **SEO fields** — Meta description and Open Graph image. These control how your article appears when shared on social
  media or in search results.

### Draft and Publish

Every article starts as a **draft**. Drafts are only visible to you in the admin.

When you're ready, change the status to **Published**. BlogWriter sets the publish date to now (or you can schedule a
future date). Published articles appear on your site and in your feeds.

You can unpublish an article at any time by switching it back to draft.

### Auto-Save

BlogWriter saves your work in the background as you type. You'll see a status indicator:

- "Saving..." — a save is in progress
- "Saved" — your latest changes are stored
- "Last saved at 2:34 PM" — shows the timestamp of the last save

Auto-save creates drafts. It won't accidentally publish anything.

### Where Articles Live

Your article's URL follows this structure:

```
yourdomain.com/blog/your-article-title
```

The slug is automatically generated from your title when you first create the article. Once published, the URL never
changes — permanent links matter.

### Automatic Backups

BlogWriter automatically backs up each article as a Markdown file with YAML frontmatter. Your content lives in the
database for fast retrieval, and as `.md` files for portability and peace of mind.

### Markdown Export (Optional)

By default, you can append `.md` to any article URL to get the raw Markdown version:

```
yourdomain.com/blog/your-article-title.md
```

This returns something like:

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

You can disable this feature in Settings if you prefer.

<x-callout type="info" title="For Developers">
  The `.md` endpoint sets `Content-Type: text/markdown` and is useful for LLMs and automated tools
  to fetch content without parsing HTML, reducing server load.
</x-callout>

---

## Notes

Notes are short posts without titles. Use them for quick thoughts, links, updates — anything that doesn't need the full
article treatment. Think tweets, but on your own site.

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

## Photos

Photos are image posts with captions. For photographers, visual bloggers, or anyone who wants to share images as
first-class content (not just inline in articles or notes).

### Uploading

Upload an image using drag-and-drop or the file picker. BlogWriter accepts common image formats (JPEG, PNG, WebP, GIF).

### Fields

- **Image** (required) — The photo itself.
- **Caption** — Text that appears with your photo. Uses the same rich editor as notes.
- **Alt text** — Describes the image for screen readers and when images can't load. Always fill this in.
- **Tags** — Same tag system as articles and notes. Create new tags on the fly.

<x-callout type="info" title="Technical Note">
  BlogWriter automatically extracts EXIF metadata from your photos when available (camera model,
  date taken, exposure settings, GPS location). This data is stored and can be displayed in your theme.
</x-callout>

### Draft and Publish

Same workflow. Draft until you're ready, then publish.

### Where Photos Live

```
yourdomain.com/photos/42
```

Photos use a numeric ID.

---

## Categories

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

## Tags

Tags provide finer-grained organization and work across **all post types** — articles, notes, and photos.

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

## Settings

The Settings page lets you update your site and author information.

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
