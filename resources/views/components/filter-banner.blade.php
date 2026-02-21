@props(['action', 'target', 'clearRoute', 'persistKey' => '', 'navigationLabel' => 'Navigate'])

@php
    $hasFilters = request('search') || request('category') || request('type') || request('status') || request('sort');
    $hasNavigation = isset($navigation) && trim((string) $navigation) !== '';
@endphp

<div x-data="{
    open: {{ $persistKey ? "\$persist(" . ($hasFilters ? 'true' : 'false') . ").as('" . $persistKey . "')" : ($hasFilters ? 'true' : 'false') }},
    navOpen: false,
}" class="mb-6">
    {{-- Toolbar row: Navigation Toggle + Filter Toggle + Clear + toolbar slot --}}
    <div class="flex items-center gap-2">
        @if($hasNavigation)
            <button @click="navOpen = !navOpen" class="btn btn-ghost btn-sm gap-1" type="button">
                <i class="ph ph-folders text-lg"></i>
                {{ $navigationLabel }}
                <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="navOpen && 'rotate-180'"></i>
            </button>
        @endif

        <button @click="open = !open" class="btn btn-ghost btn-sm gap-1" type="button">
            <i class="ph ph-funnel text-lg"></i>
            Filters
            @if($hasFilters)
                <span class="badge badge-xs badge-primary"></span>
            @endif
            <i class="ph ph-caret-down text-sm transition-transform duration-200" :class="open && 'rotate-180'"></i>
        </button>
        @if($hasFilters)
            <a href="{{ $clearRoute }}" x-target.push="{{ $target }}" class="btn btn-ghost btn-sm gap-1">
                <i class="ph ph-x text-sm"></i>
                Clear
            </a>
        @endif

        {{-- Right-aligned toolbar slot --}}
        @if(isset($toolbar))
            <div class="ml-auto flex items-center gap-2">
                {{ $toolbar }}
            </div>
        @endif
    </div>

    {{-- Collapsible navigation panel --}}
    @if($hasNavigation)
        <div x-show="navOpen" x-collapse x-cloak class="mt-3">
            {{ $navigation }}
        </div>
    @endif

    {{-- Collapsible filter form --}}
    <div x-show="open" x-collapse x-cloak class="mt-3">
        <form method="GET" action="{{ $action }}"
              x-target.push="{{ $target }}"
              @submit="$el.querySelectorAll('[name]').forEach(el => { if (!el.value) el.removeAttribute('name') })"
              class="card bg-base-100 shadow-sm card-body p-4 gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                {{ $slot }}
            </div>
        </form>
    </div>
</div>
