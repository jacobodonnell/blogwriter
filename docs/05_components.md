# Components

BlogWriter includes ready-to-use Blade components for common elements. Use them as-is in your themes, or override them by creating a file with the same name in your theme's `components/` directory.

## Available Components

- [Navbar](#navbar)
- [Breadcrumbs](#breadcrumbs)
- [Article Card](#article-card)
- [Note Card](#note-card)
- [Photo Card](#photo-card)
- [Pagination](#pagination)
- [Article Header](#article-header)
- [Table of Contents](#table-of-contents)
- [Author Bio](#author-bio)
- [Previous/Next Navigation](#previousnext-navigation)
- [Search Form](#search-form)
- [h-card (Author Identity)](#h-card-author-identity)
- [h-entry (Post Wrapper)](#h-entry-post-wrapper)
- [h-feed (Post List)](#h-feed-post-list)
- [Dark Mode Toggle](#dark-mode-toggle)
- [Back to Top Button](#back-to-top-button)
- [Reading Progress Bar](#reading-progress-bar)
- [Social Sharing Links](#social-sharing-links)

---

## Navbar

Responsive navigation with your site name, links, and mobile hamburger menu.

**Usage:**
```html
<x-navbar />
```

**Component Code:**
```html
<nav x-data="{ open: false }" class="navbar bg-base-100 shadow-sm">
    <div class="flex-1">
        <a href="/" class="btn btn-ghost text-xl">
            {{ site('name') }}
        </a>
    </div>

    <!-- Desktop nav -->
    <div class="hidden md:flex flex-none">
        <ul class="menu menu-horizontal px-1">
            <li><a href="/">Home</a></li>
            <li><a href="/blog">Articles</a></li>
            <li><a href="/notes">Notes</a></li>
            <li><a href="/photos">Photos</a></li>
        </ul>
    </div>

    <!-- Mobile hamburger -->
    <div class="flex-none md:hidden">
        <button @click="open = !open" class="btn btn-square btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile menu -->
    <div x-show="open" @click.away="open = false" class="absolute top-full left-0 right-0 bg-base-100 shadow-lg md:hidden z-50">
        <ul class="menu p-4">
            <li><a href="/">Home</a></li>
            <li><a href="/blog">Articles</a></li>
            <li><a href="/notes">Notes</a></li>
            <li><a href="/photos">Photos</a></li>
        </ul>
    </div>
</nav>
```

---

## Breadcrumbs

Shows the user's position in the site hierarchy.

**Usage:**
```html
<x-breadcrumbs :article="$article" />
```

**Component Code:**
```html
<div class="breadcrumbs text-sm">
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/blog">Articles</a></li>
        <li>{{ $article->title }}</li>
    </ul>
</div>
```

---

## Article Card

Card for article lists showing title, summary, date, reading time, and categories.

**Usage:**
```html
<x-article-card :article="$article" />
```

**Component Code:**
```html
<article class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="flex items-center gap-2 text-sm text-base-content/60">
            <time datetime="{{ $article->published_at->toDateString() }}">
                {{ $article->published_at->format('M j, Y') }}
            </time>
            <span>&middot;</span>
            <span>{{ $article->reading_time }} min read</span>
        </div>

        <h2 class="card-title">
            <a href="/blog/{{ $article->slug }}" class="hover:underline">
                {{ $article->title }}
            </a>
        </h2>

        @if ($article->summary)
            <p class="text-base-content/70">{{ $article->summary }}</p>
        @endif

        @if ($article->categories->isNotEmpty())
            <div class="card-actions mt-2">
                @foreach ($article->categories as $category)
                    <a href="/category/{{ $category->slug }}" class="badge badge-outline">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</article>
```

---

## Note Card

Compact card for note lists.

**Usage:**
```html
<x-note-card :note="$note" />
```

**Component Code:**
```html
<article class="card bg-base-100 shadow-sm">
    <div class="card-body">
        <div class="text-sm text-base-content/60">
            <time datetime="{{ $note->published_at->toDateString() }}">
                {{ $note->published_at->format('M j, Y \a\t g:i A') }}
            </time>
        </div>

        <div class="prose prose-sm mt-2">
            {!! Str::limit(strip_tags($note->content_html), 280) !!}
        </div>

        <div class="card-actions mt-2">
            <a href="/notes/{{ $note->id }}" class="link link-primary text-sm">
                Read note &rarr;
            </a>
        </div>
    </div>
</article>
```

---

## Photo Card

Thumbnail card for photo grids.

**Usage:**
```html
<x-photo-card :photo="$photo" />
```

**Component Code:**
```html
<article class="card bg-base-100 shadow-sm">
    <figure>
        <a href="/photos/{{ $photo->id }}">
            <img
                src="{{ asset('storage/' . $photo->path) }}"
                alt="{{ $photo->alt_text ?? $photo->caption ?? 'Photo' }}"
                class="w-full h-48 object-cover"
                loading="lazy"
            />
        </a>
    </figure>
    <div class="card-body p-4">
        @if ($photo->caption)
            <p class="text-sm text-base-content/70">
                {{ Str::limit($photo->caption, 100) }}
            </p>
        @endif
        <time class="text-xs text-base-content/50" datetime="{{ $photo->published_at->toDateString() }}">
            {{ $photo->published_at->format('M j, Y') }}
        </time>
    </div>
</article>
```

---

## Pagination

DaisyUI-styled pagination for paginated lists.

**Usage:**
```html
<x-pagination :items="$posts" />
```

**Component Code:**
```html
@if ($items->hasPages())
    <div class="join mt-8">
        @if ($items->onFirstPage())
            <button class="join-item btn btn-disabled">&laquo;</button>
        @else
            <a href="{{ $items->previousPageUrl() }}" class="join-item btn">&laquo;</a>
        @endif

        @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="join-item btn {{ $page == $items->currentPage() ? 'btn-active' : '' }}">
                {{ $page }}
            </a>
        @endforeach

        @if ($items->hasMorePages())
            <a href="{{ $items->nextPageUrl() }}" class="join-item btn">&raquo;</a>
        @else
            <button class="join-item btn btn-disabled">&raquo;</button>
        @endif
    </div>
@endif
```

---

## Article Header

Title, date, author, categories, and reading time for article pages.

**Usage:**
```html
<x-article-header :article="$article" />
```

**Component Code:**
```html
<header class="mb-8">
    <h1 class="text-4xl font-bold mb-4">{{ $article->title }}</h1>

    <div class="flex flex-wrap items-center gap-3 text-sm text-base-content/60">
        <span>{{ author('name') }}</span>
        <span>&middot;</span>
        <time datetime="{{ $article->published_at->toDateString() }}">
            {{ $article->published_at->format('F j, Y') }}
        </time>
        <span>&middot;</span>
        <span>{{ $article->reading_time }} min read</span>
    </div>

    @if ($article->categories->isNotEmpty())
        <div class="flex gap-2 mt-3">
            @foreach ($article->categories as $category)
                <a href="/category/{{ $category->slug }}" class="badge badge-primary badge-outline">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif
</header>
```

---

## Table of Contents

Auto-generated from headings in article content. Uses Alpine.js.

**Usage:**
```html
<x-table-of-contents />
```

**Component Code:**
```html
<nav
    x-data="{
        headings: [],
        init() {
            this.headings = [...document.querySelectorAll('.prose h2, .prose h3')].map(el => ({
                id: el.id || (el.id = el.textContent.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '')),
                text: el.textContent,
                level: el.tagName === 'H2' ? 2 : 3
            }));
        }
    }"
    x-show="headings.length > 0"
    class="bg-base-200 rounded-lg p-4 mb-8"
>
    <h2 class="font-bold text-sm uppercase tracking-wide mb-2">Contents</h2>
    <ul class="space-y-1 text-sm">
        <template x-for="heading in headings" :key="heading.id">
            <li :class="heading.level === 3 ? 'ml-4' : ''">
                <a :href="'#' + heading.id" x-text="heading.text" class="link link-hover text-base-content/70"></a>
            </li>
        </template>
    </ul>
</nav>
```

---

## Author Bio

Author name, avatar, and bio shown at the end of articles.

**Usage:**
```html
<x-author-bio />
```

**Component Code:**
```html
<div class="card bg-base-200 mt-12">
    <div class="card-body flex-row items-center gap-4">
        @if (author('avatar'))
            <img
                src="{{ author('avatar') }}"
                alt="{{ author('name') }}"
                class="w-16 h-16 rounded-full"
            />
        @endif
        <div>
            <p class="font-bold">{{ author('name') }}</p>
            @if (author('bio'))
                <p class="text-sm text-base-content/70">
                    {{ author('bio') }}
                </p>
            @endif
        </div>
    </div>
</div>
```

---

## Previous/Next Navigation

Links to the previous and next articles.

**Usage:**
```html
<x-prev-next :previous="$previous" :next="$next" />
```

**Component Code:**
```html
<nav class="flex justify-between mt-12 pt-6 border-t border-base-200">
    @if ($previous)
        <a href="/blog/{{ $previous->slug }}" class="flex flex-col items-start">
            <span class="text-xs text-base-content/50">&larr; Previous</span>
            <span class="font-medium">{{ $previous->title }}</span>
        </a>
    @else
        <div></div>
    @endif

    @if ($next)
        <a href="/blog/{{ $next->slug }}" class="flex flex-col items-end text-right">
            <span class="text-xs text-base-content/50">Next &rarr;</span>
            <span class="font-medium">{{ $next->title }}</span>
        </a>
    @endif
</nav>
```

---

## Search Form

Search form that submits via Alpine AJAX.

**Usage:**
```html
<x-search-form />
```

**Component Code:**
```html
<form x-data x-target="search-results" method="get" action="/search" class="form-control">
    <div class="input-group">
        <input type="text" name="q" placeholder="Search..." class="input input-bordered w-full" />
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>

<div id="search-results"><!-- Results load here --></div>
```

---

## h-card (Author Identity)

Microformats markup for your identity. Place on your homepage.

**Usage:**
```html
<x-h-card />
```

**Component Code:**
```html
<div class="h-card p-author">
    @if (author('avatar'))
        <img
            src="{{ author('avatar') }}"
            alt="{{ author('name') }}"
            class="u-photo w-16 h-16 rounded-full"
        />
    @endif

    <a href="{{ site('domain') }}" class="u-url p-name" rel="me">
        {{ author('name') }}
    </a>

    @if (author('bio'))
        <p class="p-note">{{ author('bio') }}</p>
    @endif

    @if (author('email'))
        <a href="mailto:{{ author('email') }}" class="u-email hidden">
            {{ author('email') }}
        </a>
    @endif
</div>
```

---

## h-entry (Post Wrapper)

Microformats markup for posts. Wraps articles, notes, and photos.

**Usage:**
```html
<x-h-entry-article :article="$article">
    <!-- Article content -->
</x-h-entry-article>

<x-h-entry-note :note="$note">
    <!-- Note content -->
</x-h-entry-note>

<x-h-entry-photo :photo="$photo">
    <!-- Photo content -->
</x-h-entry-photo>
```

**Component Code (Article):**
```html
<article class="h-entry">
    <h1 class="p-name">{{ $article->title }}</h1>

    <time class="dt-published" datetime="{{ $article->published_at->toIso8601String() }}">
        {{ $article->published_at->format('F j, Y') }}
    </time>

    <a href="{{ site('domain') }}/blog/{{ $article->slug }}" class="u-url hidden"></a>

    <div class="e-content">
        {{ $slot }}
    </div>

    @foreach ($article->categories as $category)
        <a href="/category/{{ $category->slug }}" class="p-category">{{ $category->name }}</a>
    @endforeach

    @foreach ($article->tags as $tag)
        <span class="p-category">{{ $tag->name }}</span>
    @endforeach

    <span class="p-author h-card hidden">
        <a href="{{ site('domain') }}" class="u-url p-name">
            {{ author('name') }}
        </a>
    </span>
</article>
```

---

## h-feed (Post List)

Microformats markup for post lists. Wraps your homepage feed.

**Usage:**
```html
<x-h-feed>
    @foreach ($posts as $post)
        <!-- h-entry wrapped posts -->
    @endforeach
</x-h-feed>
```

**Component Code:**
```html
<div class="h-feed">
    <span class="p-name hidden">{{ site('name') }}</span>
    <a href="{{ site('domain') }}" class="u-url hidden"></a>

    {{ $slot }}
</div>
```

---

## Dark Mode Toggle

Toggles between light and dark themes. Persists choice to localStorage.

**Usage:**
```html
<x-dark-mode-toggle />
```

**Component Code:**
```html
<div x-data="{
    dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
    toggle() {
        this.dark = !this.dark;
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
    },
    init() {
        document.documentElement.setAttribute('data-theme', this.dark ? 'dark' : 'light');
    }
}">
    <button @click="toggle()" class="btn btn-ghost btn-circle">
        <template x-if="dark">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </template>
        <template x-if="!dark">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </template>
    </button>
</div>
```

---

## Back to Top Button

Appears when user scrolls down, smooth-scrolls to top on click.

**Usage:**
```html
<x-back-to-top />
```

**Component Code:**
```html
<div x-data="{ show: false }" x-on:scroll.window="show = window.scrollY > 300">
    <button
        x-show="show"
        x-transition
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="btn btn-circle btn-primary fixed bottom-6 right-6 shadow-lg z-50"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
    </button>
</div>
```

---

## Reading Progress Bar

Shows reading progress at top of page as user scrolls.

**Usage:**
```html
<x-reading-progress />
```

**Component Code:**
```html
<div
    x-data="{ progress: 0 }"
    x-on:scroll.window="
        let el = document.querySelector('.prose');
        if (el) {
            let rect = el.getBoundingClientRect();
            let total = el.scrollHeight - window.innerHeight;
            progress = Math.min(100, Math.max(0, ((window.scrollY - el.offsetTop + window.innerHeight) / total) * 100));
        }
    "
    class="fixed top-0 left-0 right-0 z-50 h-1 bg-base-200"
>
    <div class="h-full bg-primary transition-all duration-150" :style="'width: ' + progress + '%'"></div>
</div>
```

---

## Social Sharing Links

Simple sharing links. No JavaScript SDKs or tracking.

**Usage:**
```html
<x-social-share :article="$article" />
```

**Component Code:**
```html
@php
    $url = urlencode(request()->url());
    $title = urlencode($article->title);
@endphp

<div class="flex gap-2">
    <a
        href="https://twitter.com/intent/tweet?url={{ $url }}&text={{ $title }}"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-sm btn-ghost"
    >
        Share on X
    </a>
    <a
        href="https://www.linkedin.com/sharing/share-offsite/?url={{ $url }}"
        target="_blank"
        rel="noopener noreferrer"
        class="btn btn-sm btn-ghost"
    >
        LinkedIn
    </a>
    <a
        href="mailto:?subject={{ $title }}&body={{ $url }}"
        class="btn btn-sm btn-ghost"
    >
        Email
    </a>
</div>
```

---

## Overriding Components

To customize any component, create a file with the same name in your theme's `components/` directory:

```
themes/my-theme/components/article-card.blade.php
```

Copy the component code above, modify it, and your version will be used instead of the built-in one.

#### [Up Next: *Settings*](06_settings.md)