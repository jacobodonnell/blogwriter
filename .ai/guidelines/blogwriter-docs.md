=== blogwriter-docs rules ===

# BlogWriter Documentation

Documentation and specifications for the BlogWriter platform are stored in the `docs/` directory.

## Skills Activation

IMPORTANT: Activate the `blogwriter-docs` skill when:

- User asks about BlogWriter architecture, models, or design specifications
- User asks about themes, components, or customization features
- User asks about the installation system or requirements
- User asks about configuration, settings, or IndieWeb integration
- User references "docs", "documentation", or "specifications"
- User mentions building or implementing Articles, Notes, or Photos
- User is working on theme system or component library
- User needs to understand content types or data models
- User asks about feeds, IndieAuth, or IndieWeb features

## Local Documentation

All documentation is in the `docs/` directory with YAML frontmatter (HydePHP-compatible for future static site generation).

Documentation files:
- `introduction.md` - Product vision and content types
- `installation.md` - CLI installer (working) and web installer (coming soon)
- `writing-content.md` - Content creation workflows
- `themes.md` - Theme system specification (coming soon)
- `components.md` - Component library (stub)
- `settings.md` - Configuration options
- `feeds-and-indieweb.md` - RSS/Atom/JSON feeds and IndieWeb features (coming soon)
- `architecture.md` - Tech stack, models, and design decisions
- `roadmap.md` - Feature status and milestones

## Key Reminders

- **Verify implementation status** - Many documented features are specifications to be built, not existing implementations
- **Always check codebase** - Documentation describes both current and planned features
- **"Coming Soon" callouts** indicate features not yet implemented
- **GitHub feedback welcome** - Unimplemented features invite community input on design
- **HydePHP build target** - YAML frontmatter will be used for static site generation (future work)
