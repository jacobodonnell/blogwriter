---
title: Writing Content
description: Create and manage articles and photos with BlogWriter's editors and publishing workflow.
extends: _layouts.documentation
section: content
category: content
category_order: 2
order: 3
---

# Writing Content

BlogWriter supports two content types: **articles** and **photos**. Each has its own editor and publishing workflow.

## The Admin Dashboard

After logging in at `/admin`, you'll see your dashboard with:

- Article and photo counts (published and drafts)
- Quick actions to create new content

The sidebar navigation gives you access to:

- **Articles**
- **Photos**
- **Categories**
- **Settings**
- **View Site** — opens your blog as visitors see it

---

## Articles

Articles are long-form posts with titles. Blog posts, essays, tutorials.

### The Customizer

The Customizer is BlogWriter's article editor — a Statamic-inspired split-pane layout with editing controls on the left and a live preview of your article on the right. The preview updates as you type and the pane is resizable.

As you type, your changes auto-save in the background via AJAX and the preview updates. The editor uses **EasyMDE**, a Markdown editor with toolbar formatting buttons and live Markdown preview.

### Fields

- **Title** (required) — Your article's headline. A URL slug is auto-generated from the title. You can edit the slug manually.
- **Summary** (optional) — A short description used in article lists and SEO meta tags.
- **Content** (required) — Markdown content via EasyMDE editor. A `NoH1Heading` validation rule prevents H1 headings in content (the title serves as the H1).
- **Featured Image** (optional) — Attach a photo in two ways:
  1. Select an existing photo from the dropdown
  2. Upload a new photo directly from the customizer via a modal — the photo is created and associated automatically
  - Featured images can also be set via an external URL
- **Categories** — Assign a category. Create new categories on the fly from the editor.

### Content Storage

Article content is stored as Markdown in the database. On save, double newlines are normalized. On read, collapsed newlines are expanded back, preserving code blocks. The `content_html` accessor renders Markdown to HTML for display.

### Publishing Workflow

The save button adapts to what you're about to do:

- **Save Draft** — You're working on a draft. Save and keep writing.
- **Publish** — Your draft is ready. A confirmation dialog appears — once you publish, the article is live.
- **Save Changes** — Already published? Your changes save without ceremony.
- **Unpublish** — Switch the status to draft and confirm. The article returns a 404 until republished.
- **Republish** — Bringing back an unpublished article preserves the original publish date.

### Permalinks

Articles live at:

```
yourdomain.com/articles/your-article-slug
```

The slug is generated from the title when you create the article. If you change the slug later, BlogWriter stores the old slug in a `past_slugs` JSON column. Anyone visiting the old URL gets a **301 permanent redirect** to the new address.

---

## Photos

Photos are image posts with captions. For photographers, visual bloggers, or anyone sharing images as standalone content.

Uses [Spatie Laravel MediaLibrary](https://spatie.be/docs/laravel-medialibrary) for image handling.

### Uploading

Upload an image using the file picker. BlogWriter accepts common image formats (JPEG, PNG, WebP, GIF).

MediaLibrary automatically generates three conversions:

- **thumbnail** (300×300) — For gallery views
- **medium** (768×768) — For content display
- **large** (1536×1536) — For full-size viewing

### Fields

- **Image** (required) — The photo file, managed via MediaLibrary.
- **Caption** (optional) — Markdown text displayed with the photo.
- **Alt text** (required) — Describes the image for screen readers.
- **Slug** — Auto-generated, unique identifier.

### EXIF Data

MediaLibrary extracts EXIF metadata from uploaded photos. This data (camera model, date taken, dimensions, etc.) is stored in the photo's `meta` column and can be displayed on the photo page.

### Draft and Publish

Same workflow as articles. Draft until ready, then publish.

When a photo is used as a featured image on an article and the article is set to draft, the photo is automatically detached.

### Media Serving

Draft photos are stored on a private disk and served through a controller with authentication checks, keeping unpublished media access-controlled. When a photo is published, it moves to the public disk and is served directly — no authentication required.

### Where Photos Live

```
yourdomain.com/photos/your-photo-slug
```

Photos use a slug in their URL.

---

## Categories

Categories organize your articles into broad topics. An article belongs to one category.

### Managing Categories

Go to **Categories** in the admin sidebar. You can also create categories on the fly when editing an article.

Each category gets a URL slug generated from its name:

```
yourdomain.com/categories/tech
```

Visiting a category page shows all published articles in that category, with h-feed microformats markup.

---

## Admin Features

### Articles Table

The articles index provides a sortable, filterable table:

- **Sort** by title, status, published date, or created date
- **Filter** by status (all, published, drafts)
- **Column toggles** — show or hide columns
- **Per-page pagination** — choose how many articles per page

### Dashboard

The dashboard displays content stats and quick action links.

---

## Import & Export

### Exporting Articles

Go to **Settings → Import & Export → Export Articles** to download a ZIP containing all your articles as Markdown files with YAML frontmatter, plus a `categories.yaml` file.

The ZIP is compatible with Hugo, Jekyll, Eleventy, and other static site generators.

### Importing Articles

Upload a BlogWriter export ZIP to restore articles on a fresh install or migrate from another instance. The importer:

- Restores categories from `categories.yaml` (if present)
- Warns if any article references a category not found in the database
- Lets you skip or overwrite duplicate slugs
- Preserves original `created_at`, `published_at`, `slug`, and all metadata

#### [Up Next: *Appearance*](/docs/customization/appearance)
