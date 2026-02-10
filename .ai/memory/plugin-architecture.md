# Plugin Architecture Design

## Core Concept

Plugin Store is an **interface** - multiple implementations possible (official store, GitHub, local filesystem, etc.)

## Design Pattern: Strategy Pattern

```php
interface PluginStore
{
    public function list(): Collection;
    public function find(string $slug): ?Plugin;
    public function download(string $slug): string; // returns path to downloaded .zip
    public function search(string $query): Collection;
}
```

## Multiple Store Implementations

### 1. Official BlogWriter Store (HTTP API)
```php
class BlogWriterPluginStore implements PluginStore
{
    public function __construct(
        protected string $apiUrl,
        protected Client $http
    ) {}
}
```

### 2. GitHub Store (search GitHub repos with topic "blogwriter-plugin")
```php
class GitHubPluginStore implements PluginStore
{
    // Uses GitHub API to discover plugins
}
```

### 3. Local Filesystem Store
```php
class LocalPluginStore implements PluginStore
{
    // Scans local directory for plugins
}
```

### 4. Custom Store (users can add their own)
```php
class CustomPluginStore implements PluginStore
{
    // Users can point to their own store API
}
```

## Configuration

```php
// config/blogwriter.php

'plugin_stores' => [
    'official' => [
        'driver' => 'blogwriter',
        'url' => 'https://plugins.blogwriter.dev/api',
        'enabled' => true,
    ],
    'github' => [
        'driver' => 'github',
        'enabled' => true,
        'token' => env('GITHUB_TOKEN'), // optional, for rate limits
    ],
    'local' => [
        'driver' => 'local',
        'path' => storage_path('plugins'),
        'enabled' => true,
    ],
],
```

## Store Manager (Aggregates All Stores)

```php
class PluginStoreManager
{
    protected array $stores = [];

    public function store(string $name): PluginStore
    {
        return $this->stores[$name]
            ?? throw new InvalidArgumentException("Store [{$name}] not found");
    }

    public function allStores(): array
    {
        return array_filter($this->stores, fn($store) => $store->enabled());
    }

    public function search(string $query): Collection
    {
        // Search across ALL enabled stores, merge results
        return collect($this->allStores())
            ->flatMap(fn($store) => $store->search($query))
            ->unique('slug');
    }
}
```

## Plugin Data Transfer Object

```php
class Plugin
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $description,
        public string $version,
        public string $author,
        public string $homepage,
        public ?string $downloadUrl,
        public array $requires = [], // ['php' => '8.4', 'blogwriter' => '^0.1']
        public array $tags = [],
        public ?string $store = null, // which store provided this
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
```

## Benefits of Interface Approach

1. **Extensible** - Users can create their own stores
2. **Testable** - Mock stores for testing
3. **Flexible** - Different sources (HTTP, GitHub, local, npm-style registry)
4. **Decentralized** - No single point of failure
5. **Privacy** - Local/private stores possible

## Next Steps

- [ ] Define `PluginStore` interface
- [ ] Implement `BlogWriterPluginStore` (HTTP client)
- [ ] Implement `LocalPluginStore` (filesystem)
- [ ] Build `PluginStoreManager` (aggregator)
- [ ] GitHub store can come later (optional)