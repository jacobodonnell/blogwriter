# Phase 2: Implementation (After Tests Pass)

## 1. Update Article Model

Add guarded and casts configuration in `app/Models/Article.php`:

```php
protected $guarded = [];

protected function casts(): array
{
    return [
        'published_at' => 'datetime',
        'meta' => 'array',
    ];
}
```

**Why we're casting:**
- `published_at` → `datetime`: Converts the database timestamp string into a Carbon instance, giving us helpful methods like `diffForHumans()` ("2 days ago"), `format()`, etc.
- `meta` → `array`: Converts the JSON string stored in the database into a PHP array when accessed, and converts back to JSON when saved. Without this, you'd get a string and need to manually `json_decode()` every time.

## 2. Update ArticleController::index()

Replace 'hello world' with proper query and view in `app/Http/Controllers/ArticleController.php`:

```php
public function index()
{
    $articles = Article::query()
        ->where('status', 'published')
        ->orderByDesc('published_at')
        ->select(['id', 'slug', 'title', 'summary', 'published_at', 'status'])
        ->paginate(15);

    return view('admin.articles.index', compact('articles'));
}
```

**Why:**
- Filter to published articles only
- Order by newest first
- Select only needed columns for performance
- Use pagination (15 per page)

## 3. Create index.blade.php View

Create `resources/views/admin/articles/index.blade.php` with:

- Use `<x-layouts::app :title="__('Articles')">` wrapper (matches dashboard pattern)
- Header section with title and "Create Article" button
- Responsive grid: 1 col mobile, 2 cols tablet, 3 cols desktop
- DaisyUI cards displaying: title, summary (truncated), published date, actions
- Empty state for no articles
- Pagination links

**DaisyUI Card Structure (from Context7):**
```html
<div class="card bg-base-100 shadow-xl border border-zinc-200 dark:border-zinc-700">
    <div class="card-body">
        <h2 class="card-title">{{ $article->title }}</h2>
        <p class="text-sm opacity-70 line-clamp-3">{{ $article->summary }}</p>
        <div class="text-xs opacity-60 mt-2">
            Published {{ $article->published_at->diffForHumans() }}
        </div>
        <div class="card-actions justify-end mt-4">
            <a href="{{ route('articles.show', $article) }}" class="btn btn-sm btn-ghost">View</a>
            <a href="{{ route('articles.edit', $article) }}" class="btn btn-sm btn-primary">Edit</a>
        </div>
    </div>
</div>
```

## 4. Add Navigation Item

Update `resources/views/layouts/app/header.blade.php`:

- Add to desktop navbar (after Dashboard item, ~line 16)
- Add to mobile sidebar (after Dashboard item, ~line 58)

```blade
<!-- Desktop navbar -->
<flux:navbar.item
    icon="document-text"
    :href="route('articles.index')"
    :current="request()->routeIs('articles.*')"
    wire:navigate
>
    {{ __('Articles') }}
</flux:navbar.item>

<!-- Mobile sidebar -->
<flux:sidebar.item
    icon="document-text"
    :href="route('articles.index')"
    :current="request()->routeIs('articles.*')"
    wire:navigate
>
    {{ __('Articles') }}
</flux:sidebar.item>
```

## Design Decisions

**DaisyUI Cards:**
- Using standard DaisyUI card component (card + card-body + card-title + card-actions)
- DaisyUI already installed and configured (v5.5.14)
- Provides semantic structure without custom JavaScript
- Works with dark mode via base-100 and border colors

**Layout Strategy:**
- Flux UI for navigation/header (existing pattern)
- DaisyUI for content cards (requirement specification)
- Tailwind utilities for responsive grid

**Data Display:**
- Title (primary), summary (3-line truncate), published date (relative format)
- View (ghost) and Edit (primary) action buttons
- No content_json/markdown (too large for index)
- No meta or timestamps (not essential)

**Filtering:**
- Published articles only initially
- Could add status filter later for drafts

## Verification

After implementation:

1. **Visit route:** Navigate to `/admin/articles` (logged in as user)
2. **Check database:** Query shows 5 published articles exist
3. **Verify display:**
   - Page shows grid of article cards
   - Each card displays title, summary, date, actions
   - Articles ordered newest first
   - Navigation item appears in header/sidebar
   - Dark mode styling works
4. **Test pagination:** Create 20+ articles via factory/seeder, verify pagination works
5. **Test empty state:** Delete all articles, verify empty state displays
6. **Run tests:** All tests should pass green

## Notes

- Route already exists: `Route::resource('articles', ArticleController::class)` in routes/admin.php
- Auth middleware already applied to /admin prefix
- Factory/seeder already created with sample data
- This follows "Blade + Alpine.js" architecture (no new Livewire components)
- No Alpine.js needed for this static list view