{{-- Shared navigation menu for authenticated layouts --}}
{{-- Expects Alpine.js context with: expanded, isDesktop, showTooltip(), hideTooltip() --}}

<ul class="menu gap-1">
    {{-- Public Pages --}}
    <li>
        <a href="{{ route('home') }}"
           class="{{ request()->is('/') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Home')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-house text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Home</span>
        </a>
    </li>

    <li>
        <a href="{{ route('articles.index') }}"
           class="{{ request()->is('articles*') && !request()->is('admin*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Articles')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-article text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Articles</span>
        </a>
    </li>

    <li>
        <a href="{{ route('photos.index') }}"
           class="{{ request()->is('photos') && !request()->is('admin*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Photos')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-image text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Photos</span>
        </a>
    </li>

    <li>
        <a href="{{ route('about') }}"
           class="{{ request()->is('about') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'About')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-user text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>About</span>
        </a>
    </li>

    <div class="divider my-2"></div>

    {{-- Admin Pages --}}
    <li>
        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->is('admin') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Dashboard')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-gauge text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Dashboard</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.articles.index') }}"
           class="{{ request()->is('admin/articles*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Manage Articles')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-note-pencil text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Manage Articles</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.photos.index') }}"
           class="{{ request()->is('admin/photos*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Manage Photos')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-images text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Manage Photos</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.categories.index') }}"
           class="{{ request()->is('admin/categories*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Categories')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-folder text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Categories</span>
        </a>
    </li>

    <li>
        <a href="{{ route('admin.settings') }}"
           class="{{ request()->is('admin/settings*') ? 'menu-active' : '' }}"
           :class="!expanded && isDesktop && 'justify-center'"
           @mouseenter="showTooltip($event, 'Settings')"
           @mouseleave="hideTooltip()">
            <i class="ph ph-gear text-lg"></i>
            <span x-show="expanded || !isDesktop" x-cloak>Settings</span>
        </a>
    </li>
</ul>
