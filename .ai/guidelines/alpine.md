=== alpine rules ===

# Alpine.js

Alpine.js is a lightweight JavaScript framework that provides reactive and declarative behavior directly in HTML markup.
This project uses Alpine.js v3.x for client-side interactivity.

## Architecture Decision

This project uses **Blade + Alpine.js + Alpine AJAX** for all features, including the admin panel.

- **Authentication** - Custom auth built on Laravel Fortify
- **Admin Panel** - Blade templates + Alpine.js + Alpine AJAX
- **Public Frontend** - Blade templates + Alpine.js + Alpine AJAX

## When to Use Alpine.js

- Use Alpine.js for all client-side interactivity in Blade components (both admin and public)
- Prefer Alpine.js over writing custom JavaScript for interactions (dropdowns, modals, toggles, forms)
- Use Alpine AJAX for dynamic content updates without full page reloads
- This is the primary frontend stack for the entire application

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

## Livewire

- This project does NOT use Livewire
- All interactivity is handled with Alpine.js and Alpine AJAX
- Do not create Livewire components
