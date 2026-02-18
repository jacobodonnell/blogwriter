=== plugins ===

# Plugin Architecture

BlogWriter features an open plugin architecture inspired by the IBM PC expansion bus model: publish the specification, encourage third-party innovation, and let the ecosystem flourish.

## Philosophy

**Open Infrastructure, Not a Walled Garden**

- The plugin store API specification is public
- Anyone can run a plugin store
- Official store provides curation and quality guarantee
- Third-party stores enable experimentation and niche plugins
- No platform lock-in: plugins work regardless of source

## Plugin System Overview

Plugins are standard Composer packages that extend BlogWriter functionality. They follow Laravel's package auto-discovery conventions for seamless integration.

### Core Principles

1. **Composer-Based** - Plugins are regular Composer packages
2. **Auto-Discovery** - Service providers automatically register
3. **Multiple Sources** - Install from official store, third-party stores, Packagist, or filesystem
4. **No Lock-In** - Plugins work identically regardless of installation method

## Installation Methods

### 1. Official Plugin Store (UI Command)

```bash
php artisan blogwriter:plugin:install author/package
```

- Curated, quality-guaranteed plugins
- 15% commission funds platform development
- Support and updates guaranteed
- One-click installation from admin panel

### 2. Third-Party Plugin Stores

```bash
php artisan blogwriter:plugin:install author/package --store=https://example-store.com
```

- Open ecosystem, zero commission
- Anyone can run a store
- Uses the same public API specification
- Experimental or niche plugins

### 3. Direct Composer Installation

```bash
composer require author/blogwriter-plugin
```

- Standard Composer workflow
- Free plugins on Packagist
- Package auto-discovery handles registration

### 4. Manual/Local Installation

```
plugins/
  author/
    package/
      src/
      composer.json
```

- Drop plugin folder into `plugins/` directory
- Useful for development and testing
- Auto-discovered on application boot

## Plugin Store Architecture

### The PluginStore Interface

```php
interface PluginStore
{
    public function search(string $query): Collection;
    public function find(string $package): ?Plugin;
    public function download(string $package, string $version): string;
    public function verify(string $package, string $checksum): bool;
}
```

### Store Implementations

**Official BlogWriter Plugin Store**
- `BlogWriterStore` - Default implementation
- Curated plugins, quality reviewed
- 15% commission for sustainability
- Support and security updates guaranteed

**Third-Party Stores**
- `GenericStore` - Standard implementation for any store following the public API
- Community-run stores for specific niches
- Zero commission, open experimentation
- Uses same interface as official store

**Local Development Store**
- `FilesystemStore` - Reads from local `plugins/` directory
- Development and testing
- No network requests

## Plugin Discovery & Registration

Plugins use Laravel's package auto-discovery:

1. **Service Provider Auto-Registration** - Plugin service providers are automatically loaded
2. **Hook System** - Plugins register hooks to extend functionality
3. **No Manual Configuration** - Drop in plugin, it just works

### Hook System

Plugins extend BlogWriter using a hook system:

```php
// In plugin service provider
public function boot()
{
    Hooks::register('post.published', function ($post) {
        // Send notification, update search index, etc.
    });
}
```

Available hooks:
- `post.published` - After post is published
- `post.deleted` - After post is deleted
- `admin.menu` - Add admin menu items
- `editor.toolbar` - Add editor toolbar buttons
- `theme.assets` - Add theme assets

## Business Model

### Free Plugins

- Hosted on GitHub/GitLab
- Published to Packagist
- Installed via Composer or manual filesystem
- Zero cost, open source

### Paid Plugins

**Official Store (Recommended)**
- Listed on BlogWriter.com plugin store
- 15% commission to BlogWriter
- Quality guarantee and support included
- Payment processing handled by platform
- Automated updates and licensing

**Third-Party Stores**
- Use Anystack.sh (like Filament) or similar
- Zero commission to BlogWriter
- Developer handles support and licensing
- Open ecosystem, developer keeps 85-100%

**Direct Sales**
- Sell through own website
- Use Gumroad, Lemon Squeezy, etc.
- Manual license key distribution
- Developer keeps 100% (minus payment processor fees)

## Inspiration & Influences

### Filament Plugin Model
- Composer packages for free and paid plugins
- Official store (Filament.com) for curated plugins
- Anystack.sh for indie developers selling paid plugins
- Package auto-discovery for seamless installation

### IBM PC Open Architecture
- Published technical specifications
- Encouraged third-party expansion cards
- Created an ecosystem of innovation
- Multiple vendors competing on quality

### WordPress Ecosystem
- Large plugin ecosystem
- Official repository for free plugins
- Third-party marketplaces (CodeCanyon, etc.)
- **But curated** - BlogWriter official store is quality-reviewed, not a free-for-all

## Implementation Roadmap

**Phase 1: Foundation (Post-V0.1)**
- Hook system for extensibility
- PluginStore interface
- FilesystemStore for local development

**Phase 2: Official Store**
- BlogWriter.com plugin directory
- UI for browsing and installing plugins
- Payment processing and licensing
- `blogwriter:plugin:install` command

**Phase 3: Open Ecosystem**
- Public API specification
- Documentation for running third-party stores
- Example implementation of GenericStore
- Developer tools and testing utilities

## Security & Quality

### Official Store Guarantees
- Code review for security issues
- Compatibility testing with latest BlogWriter version
- Support contact required
- Regular updates enforced

### Third-Party Stores
- Store operator responsible for vetting
- Users accept risk of unvetted plugins
- Open ecosystem enables experimentation

### Developer Guidelines
- Follow Laravel package conventions
- Include automated tests
- Document all hooks and APIs
- Semantic versioning required

## Developer Experience

### Creating a Plugin

```bash
# Use official skeleton
composer create-project blogwriter/plugin-skeleton my-plugin

# Or start from scratch with standard Laravel package structure
```

### Testing a Plugin

```bash
# Install locally for testing
mkdir -p plugins/author
ln -s /path/to/plugin plugins/author/package

# Plugin auto-discovered on next request
```

### Publishing a Plugin

1. **Free/Open Source**
   - Push to GitHub
   - Register on Packagist
   - Submit to official directory (optional)

2. **Paid Plugin**
   - Submit to official BlogWriter store (15% commission, recommended)
   - List on third-party store (Anystack.sh, 0% to BlogWriter)
   - Sell directly (Gumroad, Lemon Squeezy, etc.)

## Comparison to Other Platforms

| Feature | BlogWriter | WordPress | Filament | Ghost |
|---------|------------|-----------|----------|-------|
| Plugin Type | Composer packages | PHP files in wp-content/plugins | Composer packages | npm packages |
| Official Store | Yes (curated) | Yes (open) | Yes (curated) | No |
| Third-Party Stores | Yes (open spec) | Yes (CodeCanyon, etc.) | Via Anystack | Limited |
| Free Plugins | Packagist, filesystem | WordPress.org | Packagist | npm |
| Paid Plugins | Official store (15%) or Anystack (0%) | CodeCanyon, own site | Official or Anystack | Limited ecosystem |
| Auto-Discovery | Yes | No | Yes | Yes |

## FAQ

**Q: Can I sell plugins without paying commission?**
A: Yes. Use a third-party store like Anystack.sh, or sell directly via Gumroad/Lemon Squeezy.

**Q: Why would I use the official store if there's a 15% commission?**
A: Official store provides: customer trust, payment processing, automatic updates, support infrastructure, and prominent visibility.

**Q: Can I run my own plugin store?**
A: Yes. The plugin store API specification is public. Run your own store for niche plugins or specific communities.

**Q: Do plugins from third-party stores work the same?**
A: Yes. All plugins use the same interface and auto-discovery. Installation method doesn't affect functionality.

**Q: Is there a review process?**
A: Official store: Yes, quality and security review. Third-party stores: Varies by store operator.

**Q: Can I install plugins manually?**
A: Yes. Drop plugin folder in `plugins/` directory, or `composer require` directly. Both work identically to store installation.
