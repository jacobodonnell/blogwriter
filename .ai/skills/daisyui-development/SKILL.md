---
name: daisyui-development
description: "Build UIs with DaisyUI v5 component library on Tailwind CSS v4. Activates when working with DaisyUI components (btn, card, modal, alert, badge, table, navbar, dropdown, tabs), form elements (fieldset, input, select, file-input), theme switching, data-theme, or when the user mentions DaisyUI, component library, or themed UI components."
---

# DaisyUI Development

Build component-based UIs using DaisyUI v5 on Tailwind CSS v4.

## When to Apply

Activate this skill when:

- Adding or modifying DaisyUI components (btn, card, modal, alert, badge, table, etc.)
- Building or restyling forms with DaisyUI form elements (fieldset, input, select, file-input)
- Working with navigation components (navbar, menu, dropdown, breadcrumbs, tabs)
- Implementing modals or dialog components
- Working with theme switching or `data-theme`
- The user mentions DaisyUI, component library, or themed UI components

## Documentation

Use `search-docs` for detailed DaisyUI v5 patterns and documentation.

## Project Configuration

DaisyUI is configured as a CSS plugin in `resources/css/app.css` — there is no JavaScript config file:

```css
@plugin "daisyui" {
    themes: light --default,
        dark --prefersdark,
        cupcake,
        /* ... 35 themes total */
        silk;
}
```

## Theme System

Themes are applied via the `data-theme` attribute on `<html>`:

```html
<html data-theme="cupcake">
```

This project manages theme switching with Alpine.js and persists the selection in `localStorage`. Always use semantic color tokens so components adapt automatically to theme changes.

## Semantic Color Tokens

Always use these instead of hardcoded Tailwind colors:

| Token | Purpose |
|-------|---------|
| `base-100` | Page background |
| `base-200` | Slightly darker background (cards, sidebars) |
| `base-300` | Even darker background (borders, dividers) |
| `base-content` | Default text on base backgrounds |
| `primary` / `primary-content` | Primary actions and text on primary |
| `secondary` / `secondary-content` | Secondary actions |
| `accent` / `accent-content` | Accent highlights |
| `neutral` / `neutral-content` | Neutral backgrounds |
| `info` / `info-content` | Informational elements |
| `success` / `success-content` | Success states |
| `warning` / `warning-content` | Warning states |
| `error` / `error-content` | Error states |

Usage: `bg-primary`, `text-primary-content`, `border-base-300`, `btn-error`, etc.

## Critical v5 Changes

### Forms: `fieldset` replaces `form-control`

DaisyUI v5 uses native `<fieldset>` with `fieldset` and `fieldset-legend` classes instead of the v4 `form-control`/`label` pattern:

```html
<!-- v5 (use for new code) -->
<fieldset class="fieldset">
    <legend class="fieldset-legend">Email</legend>
    <input type="email" class="input" placeholder="you@example.com" />
    <p class="fieldset-label">We'll never share your email</p>
</fieldset>

<!-- v4 (exists in codebase, don't convert unless asked) -->
<div class="form-control">
    <label class="label"><span class="label-text">Email</span></label>
    <input type="email" class="input input-bordered" />
</div>
```

Both patterns exist in this project. Use `fieldset` for **new** forms. Do not convert existing `form-control` usage unless explicitly asked.

### Modals: Native `<dialog>` with `.showModal()`

DaisyUI v5 modals use the native `<dialog>` element:

```html
<dialog id="my_modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Title</h3>
        <p class="py-4">Content here</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn">Close</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<button class="btn" onclick="my_modal.showModal()">Open</button>
```

This project has a reusable `<x-editor-modal>` Blade component at `resources/views/components/editor-modal.blade.php`. **Use it instead of raw dialog HTML** when building modals:

```blade
<x-editor-modal id="confirm_delete" title="Confirm Delete" maxWidth="max-w-sm">
    <p>Are you sure?</p>
    <x-slot:actions>
        <button class="btn btn-error" @click="deleteItem()">Delete</button>
    </x-slot:actions>
</x-editor-modal>

<button class="btn" onclick="confirm_delete.showModal()">Delete</button>
```

### Collapse: `<details>` / `<summary>`

DaisyUI v5 uses native HTML disclosure elements:

```html
<details class="collapse bg-base-200">
    <summary class="collapse-title font-semibold">Click to expand</summary>
    <div class="collapse-content">Hidden content</div>
</details>
```

## Common Component Patterns

### Buttons

```html
<button class="btn">Default</button>
<button class="btn btn-primary">Primary</button>
<button class="btn btn-outline btn-secondary">Outlined</button>
<button class="btn btn-sm">Small</button>
<button class="btn btn-ghost">Ghost</button>
```

### Cards

```html
<div class="card bg-base-200 shadow-sm">
    <div class="card-body">
        <h2 class="card-title">Title</h2>
        <p>Content</p>
        <div class="card-actions justify-end">
            <button class="btn btn-primary">Action</button>
        </div>
    </div>
</div>
```

### Badges

```html
<span class="badge">Default</span>
<span class="badge badge-primary">Primary</span>
<span class="badge badge-outline">Outlined</span>
```

### Alerts

```html
<div role="alert" class="alert alert-info">
    <span>Info message here.</span>
</div>
<div role="alert" class="alert alert-error">
    <span>Error message here.</span>
</div>
```

### Navbar

```html
<div class="navbar bg-base-100">
    <div class="navbar-start">
        <a class="btn btn-ghost text-xl">BlogWriter</a>
    </div>
    <div class="navbar-end">
        <button class="btn btn-primary">Login</button>
    </div>
</div>
```

### Dropdown (with Alpine.js)

```html
<div class="dropdown" x-data="{ open: false }">
    <button @click="open = !open" class="btn m-1">Options</button>
    <ul x-show="open" @click.outside="open = false" x-cloak
        class="dropdown-content menu bg-base-200 rounded-box z-1 w-52 p-2 shadow-sm">
        <li><a>Item 1</a></li>
        <li><a>Item 2</a></li>
    </ul>
</div>
```

### Table

```html
<div class="overflow-x-auto">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th></tr>
        </thead>
        <tbody>
            <tr><td>John</td><td>john@example.com</td></tr>
        </tbody>
    </table>
</div>
```

### Tabs

```html
<div role="tablist" class="tabs tabs-bordered">
    <a role="tab" class="tab tab-active">Tab 1</a>
    <a role="tab" class="tab">Tab 2</a>
</div>
```

### Breadcrumbs

```html
<div class="breadcrumbs text-sm">
    <ul>
        <li><a>Home</a></li>
        <li><a>Articles</a></li>
        <li>Current Page</li>
    </ul>
</div>
```

### Join (grouped elements)

```html
<div class="join">
    <button class="btn join-item">Left</button>
    <button class="btn join-item">Center</button>
    <button class="btn join-item">Right</button>
</div>
```

### Avatar

```html
<div class="avatar">
    <div class="w-12 rounded-full">
        <img src="/avatar.jpg" alt="User avatar" />
    </div>
</div>
```

### Divider

```html
<div class="divider">OR</div>
```

## Common Pitfalls

- **No JS config file** — DaisyUI v5 is configured via CSS `@plugin`, not `tailwind.config.js`
- **Don't use `form-control` for new forms** — Use `fieldset` / `fieldset-legend` / `fieldset-label`
- **Use `.showModal()` for modals** — Don't toggle visibility with classes; use native dialog API
- **Always use semantic colors** — `bg-primary` not `bg-blue-500`; this ensures theme compatibility
- **Add `x-cloak` to Alpine-toggled elements** — Prevents flash of content before Alpine initializes
- **Use `<x-editor-modal>` for modals** — This project has a reusable Blade component; prefer it over raw dialog markup
- **`input-bordered` is v4** — In v5, inputs have borders by default; omit `input-bordered`
