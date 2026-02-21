{{-- Shared navigation menu for authenticated layouts --}}
{{-- Expects Alpine.js context with: expanded, isDesktop, showTooltip(), hideTooltip() --}}

<ul class="menu gap-1">
    {{-- Public Pages --}}
    <x-nav-item href="{{ route('home') }}" icon="house" label="Home" :active="request()->is('/')" />
    <x-nav-item href="{{ route('articles.index') }}" icon="article" label="Articles"
                :active="request()->is('articles*') && !request()->is('admin*')" />
    <x-nav-item href="{{ route('photos.index') }}" icon="image" label="Photos"
                :active="request()->is('photos') && !request()->is('admin*')" />
    <x-nav-item href="{{ route('categories.index') }}" icon="folder-open" label="Categories"
                :active="request()->is('categories*') && !request()->is('admin*')" />
    <x-nav-item href="{{ route('about') }}" icon="user" label="About" :active="request()->is('about')" />

    <div class="divider my-2"></div>

    {{-- Admin Pages --}}
    <x-nav-item href="{{ route('admin.dashboard') }}" icon="gauge" label="Dashboard" :active="request()->is('admin')" />
    <x-nav-item href="{{ route('admin.articles.index') }}" icon="note-pencil" label="Manage Articles"
                :active="request()->is('admin/articles*')" />
    <x-nav-item href="{{ route('admin.photos.index') }}" icon="images" label="Manage Photos"
                :active="request()->is('admin/photos*')" />
    <x-nav-item href="{{ route('admin.categories.index') }}" icon="folder" label="Categories"
                :active="request()->is('admin/categories*')" />
    <x-nav-item href="{{ route('admin.settings.profile') }}" icon="gear" label="Settings"
                :active="request()->is('admin/settings*')" />
</ul>
