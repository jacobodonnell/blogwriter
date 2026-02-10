# BlogWriter Project Memory

## Architecture Decisions

### Plugins (Changed Mind - January 2026)
- **REVERSAL:** Originally "no plugins ever" → Now building plugin system with official store
- Ship with official BlogWriter Plugin Store API wired up in config by default
- Want to attract contributors early
- Plugins will extend functionality beyond themes

### Development Approach
- **Alpha releases** - Ship fast, iterate with community
- **IndieWeb essentials first** - Focus on protocols before polish
- **AI-assisted development** - Claude Code + Kimi K2.5 w/ OpenCode for rapid dev
- **Early contributors** - Want community involvement from the start

### Tech Stack Decisions
- Laravel 12 + PHP 8.4 + SQLite
- Alpine.js + Alpine AJAX (no Livewire except auth)
- Blade + Tailwind CSS v4 + DaisyUI v5
- Laravel Fortify for auth (custom Blade+Alpine UI)
- Editor.js for articles (block editor)
- Markdown for notes + backups

### Content Strategy
- **Docs-first approach** - Comprehensive docs already written in Jigsaw SSG
- Docs serve as roadmap for development
- Eventually use Jigsaw Remote Collections to pull from app API
- Makes docs LLM-consumable for AI-assisted development

## Core Philosophy

"Remember when *we* owned the internet?"

- IndieWeb-native from day one (not bolted on)
- Own your content, own your domain
- Substack alternative without 10% platform fee
- Single-author personal blogs (not multi-tenant)
- SQLite for simplicity and portability
- Works on $3-5/month shared hosting

## Success Metric for V0.1

Personal site runs on BlogWriter and validates on IndieWeb validators.

## Links to Detailed Notes

- See `development-strategy.md` for phased approach
- See `plugin-system.md` for plugin architecture notes (when created)
- See `indieweb-implementation.md` for protocol details (when created)