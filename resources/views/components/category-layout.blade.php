@props([
    'category',
    'categories',
    'children',
    'articles',
    'photos',
    'currentType',
    'articleCount',
    'photoCount',
    'feedUrl',
    'searchPlaceholder' => 'Search content...',
])

@php
    $isRoot = is_null($category);
    $title = $isRoot ? 'Categories' : $category->name;
    $pageTitle = $title . ' - ' . config('app.name');
$parentUrl = match (true) {
        $isRoot => null,
        $category->parent !== null => $category->parent->permalink(),
        default => route('categories.index'),
    };
    $parentLabel = match (true) {
        $isRoot => null,
        $category->parent !== null => $category->parent->name,
        default => 'All Categories',
    };
@endphp

{{-- h-feed for IndieWeb --}}
<div class="h-feed max-w-4xl mx-auto">

    {{-- Alpine AJAX morph target --}}
    <div id="category-content" x-merge="morph" data-page-title="{{ $pageTitle }}">

        {{-- Breadcrumbs --}}
        @if($isRoot)
            <x-breadcrumb :items="[['label' => 'Categories']]" />
        @else
            <x-breadcrumb>
                <li><a href="{{ route('categories.index') }}" x-target.push="category-content" class="link link-hover">Categories</a></li>
                @foreach($category->ancestors() as $ancestor)
                    <li><a href="{{ $ancestor->permalink() }}" x-target.push="category-content" class="link link-hover">{{ $ancestor->name }}</a></li>
                @endforeach
                <li class="text-base-content/60">{{ $category->name }}</li>
            </x-breadcrumb>
        @endif

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold leading-tight">
                    <i class="ph ph-{{ $isRoot ? 'folders' : 'folder' }} text-primary mr-2"></i>
                    {{ $title }}
                </h1>

                @if(!$isRoot && $category->description)
                    <p class="text-base-content/70 text-lg max-w-2xl mt-2">{{ $category->description }}</p>
                @endif

                <p class="text-sm text-base-content/60 mt-2">
                    {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                    @if($photoCount > 0)
                        &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                    @endif
                </p>
            </div>
            @auth
                <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                    <i class="ph ph-gear text-lg"></i>
                    Manage Categories
                </a>
            @endauth
        </header>

        <x-category-feed
            :feedUrl="$feedUrl"
            :categories="$categories"
            :children="$children"
            :articles="$articles"
            :photos="$photos"
            :currentType="$currentType"
            :articleCount="$articleCount"
            :photoCount="$photoCount"
            :searchPlaceholder="$searchPlaceholder"
            :parentUrl="$parentUrl"
            :parentLabel="$parentLabel"
            :category="$category"
        />

    </div>
</div>
