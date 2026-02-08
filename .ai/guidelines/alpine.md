=== alpine rules ===

# Alpine.js

Alpine.js is a lightweight JavaScript framework that provides reactive and declarative behavior directly in HTML markup.
This project uses Alpine.js v3.x for client-side interactivity.

## Architecture Decision

This project is built on the **Livewire Starter Kit for authentication**, but uses **Blade + Alpine.js + Alpine AJAX** for all other features.

- **Authentication** - Use Livewire (already built with starter kit, working out of the box)
- **Everything else** - Use Blade templates + Alpine.js + Alpine AJAX

## When to Use Alpine.js

- Use Alpine.js for all client-side interactivity in Blade components
- Prefer Alpine.js over writing custom JavaScript for interactions (dropdowns, modals, toggles, forms)
- Use Alpine AJAX for dynamic content updates without full page reloads
- This is the primary frontend stack for non-auth features

## Alpine AJAX

This project uses Alpine AJAX (imacrayon/alpine-ajax) for making asynchronous HTTP requests and updating page content
without full page reloads. This is the preferred approach for dynamic features.

## Skills Activation

IMPORTANT: Activate the `alpine-development` skill when:

- Creating or modifying Alpine.js components
- Working with Alpine directives (x-data, x-show, x-if, x-on, x-model, etc.)
- Implementing Alpine AJAX functionality (x-target, x-merge, x-headers)
- Adding client-side interactivity to Blade components
- Building forms or links that use AJAX requests
- The user mentions Alpine, Alpine AJAX, or client-side interactivity

## Integration with Livewire

- **Auth features only** - Livewire components are used exclusively for authentication (login, register, password reset, etc.)
- **All other features** - Use Blade + Alpine.js + Alpine AJAX instead of creating new Livewire components
- Do not create new Livewire components unless working on authentication features
