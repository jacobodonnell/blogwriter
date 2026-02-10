---
name: alpine-development
description: Build interactive UIs with Alpine.js and Alpine AJAX, including reactive components, AJAX requests, and dynamic content updates.
---

# Alpine.js Development

Build reactive, declarative user interfaces using Alpine.js (v3.x) and Alpine AJAX for asynchronous content updates.

## When to use this skill

Use this skill when:

- Creating or modifying Alpine.js components
- Working with Alpine directives (x-data, x-show, x-if, x-on, x-model, x-bind, x-for, x-ref, etc.)
- Implementing AJAX functionality with Alpine AJAX (x-target, x-merge, x-headers)
- Adding client-side interactivity to Blade components
- Building dynamic forms, dropdowns, modals, or toggles
- Handling asynchronous HTTP requests without page reloads
- The user mentions Alpine, Alpine AJAX, or client-side interactivity

## Architecture Context

This project uses **Blade + Alpine.js + Alpine AJAX** as the primary frontend stack for the entire application.
Authentication is built with Laravel Fortify using custom Blade + Alpine.js UI (not Livewire).

## Core Alpine.js Concepts

### Declaring Component State with x-data

The `x-data` directive defines a reactive data scope for a component:

```html

<div x-data="{ open: false, count: 0 }">
    <!-- Component content with access to 'open' and 'count' -->
</div>
```

### Event Handling with x-on (@)

Listen to DOM events using `x-on:` or the `@` shorthand:

```html

<button @click="count++">Increment</button>
<button x-on:click="open = !open">Toggle</button>
<div @click.outside="open = false">Closes when clicking outside</div>
```

Common event modifiers:

- `.prevent` - preventDefault()
- `.stop` - stopPropagation()
- `.outside` - trigger when clicking outside element
- `.window` - listen on window object
- `.once` - remove listener after first invocation

### Displaying Data with x-text and x-html

```html
<span x-text="count"></span>
<div x-html="rawHtmlContent"></div>
```

### Two-Way Data Binding with x-model

Bind input values to Alpine data:

```html

<div x-data="{ message: '' }">
    <input type="text" x-model="message">
    <span x-text="message"></span>
</div>
```

### Conditional Rendering

**x-show** - Toggle visibility with CSS (element stays in DOM):

```html

<div x-show="open">Content...</div>
```

**x-if** - Add/remove from DOM (must be on `<template>` tag):

```html

<template x-if="open">
    <div>Content...</div>
</template>
```

### Looping with x-for

Iterate over arrays (must be on `<template>` tag):

```html

<div x-data="{ items: ['apple', 'banana', 'orange'] }">
    <template x-for="item in items">
        <li x-text="item"></li>
    </template>
</div>
```

### Component References with x-ref

Access DOM elements directly:

```html

<div x-data>
    <input x-ref="emailInput" type="email">
    <button @click="$refs.emailInput.focus()">Focus Email</button>
</div>
```

## Alpine AJAX

Alpine AJAX extends Alpine.js with directives for making HTTP requests and updating page content.

### Basic AJAX with x-target

The `x-target` directive specifies which element(s) to update with the response:

```html

<ul id="comments">
    <li>Comment #1</li>
</ul>
<form x-target="comments" method="post" action="/comment">
    <input name="text" required/>
    <button>Submit</button>
</form>
```

**Multiple targets:**

```html
<h2>Comments (<span id="comments_count">1</span>)</h2>
<ul id="comments">
    <li>Comment #1</li>
</ul>
<form x-target="comments comments_count" method="post" action="/comment">
    <input name="comment" required/>
    <button>Submit</button>
</form>
```

### AJAX Links

Links can also make AJAX requests:

```html
<a href="/contacts/1/edit" x-target="contact_1">Edit</a>
```

### Custom Headers with x-headers

Add custom HTTP headers to requests:

```html

<form method="post" action="/comments"
      x-target="comments"
      x-headers="{'Custom-Header': 'Value'}">
    <!-- form fields -->
</form>
```

Alpine AJAX automatically includes these headers:

- `X-Alpine-Request: true` - Identifies Alpine AJAX requests
- `X-Alpine-Target: {target-ids}` - Lists target element IDs

### Merge Strategies with x-merge

Control how response content replaces existing content:

**Replace (default)** - Replace entire element:

```html

<div id="content" x-target>
    <!-- Will be completely replaced -->
</div>
```

**Append** - Add new content to the end:

```html

<ul id="messages" x-merge="append">
    <li>First message</li>
    <!-- New messages appended here -->
</ul>
```

**Prepend** - Add new content to the beginning:

```html

<ul id="notifications" x-merge="prepend">
    <!-- New notifications prepended here -->
    <li>Old notification</li>
</ul>
```

**Morph** - Intelligently merge content (requires @alpinejs/morph plugin):

```html

<div id="contacts" x-merge="morph">
    <!-- Form state and attributes preserved during update -->
</div>
```

The morph strategy preserves:

- Input focus states
- Form values
- Scroll positions
- Selected elements

Install morph plugin:

```bash
npm install @alpinejs/morph
```

### Inline Edit Pattern

Dynamically switch between view and edit modes:

```html

<div id="contact_1" x-merge.transition>
    <p><strong>Name:</strong> John Doe</p>
    <p><strong>Email:</strong> john@example.com</p>
    <a href="/contacts/1/edit" x-target="contact_1">Edit</a>
</div>
```

When the edit link is clicked, server returns the edit form:

```html

<form id="contact_1" x-target x-merge.transition method="put" action="/contacts/1">
    <input name="first_name" value="John">
    <input name="email" value="john@example.com">
    <button>Update</button>
    <a href="/contacts/1" x-target="contact_1">Cancel</a>
</form>
```

## Global State with Alpine.store

Share state across multiple components:

```javascript
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: false,
        toggle() {
            this.on = !this.on
        }
    })
})
```

Access in components:

```html

<div x-data>
    <button @click="$store.darkMode.toggle()">Toggle Dark Mode</button>
    <div x-show="$store.darkMode.on">Dark mode content</div>
</div>
```

## Component Communication with $dispatch

Send custom events between components:

```html

<div x-data="{ title: 'Hello' }"
     @set-title.window="title = $event.detail">
    <h1 x-text="title"></h1>
</div>

<div x-data>
    <button @click="$dispatch('set-title', 'Hello World!')">
        Update Title
    </button>
</div>
```

## No Livewire

**Important:** This project does NOT use Livewire at all. Authentication is built with Laravel Fortify using custom Blade + Alpine.js UI.

All interactivity across the entire application (public site and admin panel) uses:
- Blade templates for rendering
- Alpine.js for client-side reactivity
- Alpine AJAX for server communication

## Common Patterns

### Dropdown Menu

```html

<div x-data="{ open: false }">
    <button @click="open = !open">Menu</button>
    <div x-show="open" @click.outside="open = false">
        <a href="#">Option 1</a>
        <a href="#">Option 2</a>
    </div>
</div>
```

### Modal Dialog

```html

<div x-data="{ showModal: false }">
    <button @click="showModal = true">Open Modal</button>

    <template x-if="showModal">
        <div @click.self="showModal = false" class="modal-backdrop">
            <div class="modal-content">
                <h2>Modal Title</h2>
                <button @click="showModal = false">Close</button>
            </div>
        </div>
    </template>
</div>
```

### Infinite Scroll with AJAX

```html

<table>
    <tbody id="records" x-merge="append">
    <tr>
        <td>Record 1</td>
    </tr>
    </tbody>
</table>

<a href="/records?page=2" x-target="records">Load More</a>
```

### Live Search with AJAX

```html

<div id="search-results" x-merge="morph">
    <form action="/search" x-target="search-results">
        <input name="q" type="search" @input.debounce="$el.form.requestSubmit()">
    </form>
    <ul>
        <!-- Results here -->
    </ul>
</div>
```

## Server-Side Response Requirements

For Alpine AJAX to work correctly:

1. **Return only the target element** - Response should contain the element with matching ID
2. **Preserve element ID** - The returned element must have the same `id` as the target
3. **Include child content** - All child elements will be used to update the target

Example Laravel controller response:

```php
public function update(Request $request)
{
    $comment = Comment::create($request->all());

    // Return partial view with target element
    return view('partials.comments', [
        'comments' => Comment::latest()->get()
    ]);
}
```

Partial view:

```blade
<ul id="comments">
    @foreach($comments as $comment)
        <li>{{ $comment->text }}</li>
    @endforeach
</ul>
```

## Best Practices

1. **Keep components small** - Each `x-data` scope should manage a single concern
2. **Use semantic HTML** - Alpine enhances HTML, don't replace it
3. **Prefer Alpine over custom JS** - Use Alpine directives before writing vanilla JavaScript
4. **Test without JavaScript** - Ensure forms work with JS disabled (progressive enhancement)
5. **Use appropriate merge strategies** - Choose between replace, append, prepend, and morph based on use case
6. **Validate on server** - Always validate AJAX requests server-side, even if you validate client-side
7. **Handle errors gracefully** - Provide user feedback when AJAX requests fail
8. **Follow existing patterns** - Check sibling components for established conventions

## Debugging

Access Alpine data in browser console:

```javascript
// Get Alpine data for an element
Alpine.$data(document.querySelector('[x-data]'))

// Watch for Alpine events
window.addEventListener('alpine:init', () => console.log('Alpine initialized'))
```

## Common Pitfalls

- **Using x-if without `<template>`** - x-if must be on a template tag
- **Using x-for without `<template>`** - x-for must be on a template tag
- **Forgetting x-target** - Forms/links need x-target for AJAX behavior
- **Mismatched IDs** - Server response element ID must match x-target value
- **Not preserving state** - Use x-merge="morph" when you need to preserve form state during updates
