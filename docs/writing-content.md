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

The Customizer is BlogWriter's article editor. It has four layout modes, a Tiptap WYSIWYG editor with a markdown toggle,
a revision history browser, and AJAX auto-save. You can write in a distraction-free fullscreen, a side-by-side split
view, a traditional form layout, or a preview-only mode — and switch between them from the toolbar.

### Editor Modes

The Customizer offers four distinct modes. Your choice persists to `localStorage` and syncs with the default set in
Settings.

- **Fullscreen** — Distraction-free writing. A single-line toolbar sits at the top with an auto-growing title area and
  the content editor below. The featured image displays below the title. Settings and revision history slide in as
  overlays from the right edge. This is the default mode.
- **Split** — Editor on the left, live preview on the right. The divider between the two panels is draggable so you can
  resize them. On mobile, the editor and preview are separate tabs.
- **Classic** — A traditional form layout with title, slug, content, and summary fields in one column and a sidebar for
  categories and featured images. Desktop only — on mobile this falls back to split mode.
- **Preview** — Full-width live preview of your article. An edge tab on the left lets you reopen the editor panel.

Switch modes using the collapse buttons at the left end of the toolbar (Split, Classic, Preview). In non-fullscreen modes
the same buttons appear with the addition of Fullscreen.

### Markdown Mode

Click the code icon in the toolbar to toggle between WYSIWYG and raw markdown editing. In markdown mode, the formatting
toolbar is hidden and you edit the underlying markdown directly in a plain textarea. Toggle back to return to the rich
editor.

### Revision History

Every time you save an article where the title or content has changed, BlogWriter automatically creates a revision.

Open the revision browser by clicking the clock icon in the toolbar (only visible when revisions exist). A panel slides
in from the right listing all past revisions with human-readable timestamps.

From the revision browser you can:

- **Preview** a revision — loads it into the editor in read-only mode with a "Viewing revision" banner
- **Restore** a revision — replaces the current title and content with the revision's snapshot
- **Delete** a revision — permanently removes it (with confirmation)

Press the back arrow or click away to exit revision browsing and return to your unsaved working content.

**How revisions are stored:** The first revision for an article stores a full snapshot of the title and content. Every
subsequent revision stores a unified diff against the previous state. When you preview or restore a revision, BlogWriter
replays the diff chain to reconstruct the full content. This keeps storage efficient while preserving complete history.

**Export and import:** Revisions are included when you export articles. Each revision is exported as a full markdown file
(diffs are reconstructed to snapshots). On import, the revision chain is rebuilt with diffs regenerated.

### Keyboard Shortcuts

| Shortcut | Action |
|---|---|
| Cmd/Ctrl + S | Save article |
| Cmd/Ctrl + Z | Undo |
| Cmd/Ctrl + Shift + Z | Redo |

### Editor Toolbar

The WYSIWYG toolbar includes: **Bold**, **Italic**, **H2**, **H3**, **H4**, **H5**, **Blockquote**, **Bullet list**,
**Ordered list**, **Link** (dialog), **Image** (dialog), **Inline code**, **Horizontal rule**, **YouTube embed**
(dialog), **Undo**, **Redo**, and the **Markdown toggle**.

When a figure is selected, a contextual toolbar appears with **Full Width** toggle, **Edit Image**, and **Remove Image**
buttons.

### Editor Features

- **Word count** — Displayed in the status bar below the editor
- **Unsaved changes detection** — A browser warning prevents you from navigating away with unsaved work
- **Draft revert buttons** — Individual revert buttons on the title, slug, content, and summary fields let you discard
  changes to a single field
- **Featured image preview** — The featured image displays inline in fullscreen mode and in the sidebar in classic mode

### Images

Markdown is the underlying storage format, but all syntax is handled transparently:

- **YouTube embeds** — Click the embed button, paste a URL, and it renders as a responsive iframe. Stored as
  `@[youtube](url)` internally.
- **Resizable figures** — Images in the editor are wrapped in `<figure>` elements with drag handles on the left and
  right edges. Drag to resize (minimum 150px, snaps to full-width at 98%+). Click an image to access the contextual
  toolbar for full-width toggle, editing, or removal.
- **Captions** — Each figure has a caption area below the image. Click "Add a caption…" to type. Captions are stored in
  extended markdown syntax: `![alt|width:50%|caption:\`text\`](url)`.
- **Image dialog** — The image button opens a dialog for URL and alt text input. Alt text is required.

### Fields

- **Title** (required) — Your article's headline. A URL slug is auto-generated from the title. You can edit the slug
  manually.
- **Summary** (optional) — A short description used in article lists and SEO meta tags.
- **Content** (required) — Rich text via Tiptap editor. A `NoH1Heading` validation rule prevents H1 headings in
  content (the title serves as the H1).
- **Featured Image** (optional) — Attach a photo in two ways:
    1. Select an existing photo from the dropdown
    2. Upload a new photo directly from the customizer via a modal — the photo is created and associated automatically

    - Featured images can also be set via an external URL
- **Categories** — Assign a category. Create new categories on the fly from the editor.

### Content Storage

Article content is stored as Markdown in the database. On save, double newlines are normalized. On read, collapsed
newlines are expanded back, preserving code blocks. The `content_html` accessor renders Markdown to HTML for display.

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

The slug is generated from the title when you create the article. If you change the slug later, BlogWriter stores the
old slug in a `past_slugs` JSON column. Anyone visiting the old URL gets a **301 permanent redirect** to the new
address.

---

## Photos

Photos are image posts with captions. For photographers, visual bloggers, or anyone sharing images as standalone
content.

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
- **Category** (optional) — Assign a category to organize photos.
- **Slug** — Auto-generated, unique identifier.

### EXIF Data

MediaLibrary extracts EXIF metadata from uploaded photos. This data (camera model, date taken, dimensions, etc.) is
stored in the photo's `meta` column and can be displayed on the photo page.

### Draft and Publish

Same workflow as articles. Draft until ready, then publish.

When a photo is used as a featured image on an article and the article is set to draft, the photo is automatically
detached.

### Media Serving

Draft photos are stored on a private disk and served through a controller with authentication checks, keeping
unpublished media access-controlled. When a photo is published, it moves to the public disk and is served directly — no
authentication required.

### Where Photos Live

```
yourdomain.com/photos/your-photo-slug
```

Photos use a slug in their URL. If you change a photo's slug, BlogWriter stores old slugs in a `past_slugs` JSON column
and returns a **301 permanent redirect** from the old URL to the new one — the same behavior as articles.

---

## Categories

Categories organize your content into broad topics. Both articles and photos can belong to a category.

### Hierarchy

Categories support parent/child subcategories. A category can have a parent category, creating a tree structure. The
admin panel includes a **Category Explorer** for browsing the hierarchy.

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

Go to **Settings → Import & Export → Export Articles** to download a ZIP containing all your articles as Markdown files
with YAML frontmatter, plus a `categories.yaml` file.

The ZIP is compatible with Hugo, Jekyll, Eleventy, and other static site generators.

### Importing Articles

Upload a BlogWriter export ZIP to restore articles on a fresh install or migrate from another instance. The importer:

- Restores categories from `categories.yaml` (if present)
- Warns if any article references a category not found in the database
- Lets you skip or overwrite duplicate slugs
- Preserves original `created_at`, `published_at`, `slug`, and all metadata

#### [Up Next: *Appearance*](appearance.md)
