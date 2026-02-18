<x-layouts.public :title="$category->name . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :title="$category->name . ' - ' . config('app.name')" :description="$category->description" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">

        {{-- Alpine AJAX morph target --}}
        <div id="category-content" x-merge="morph" data-page-title="{{ $category->name }} - {{ config('app.name') }}">

            {{-- Category Header --}}
            <header class="mb-8">
                <nav class="text-sm breadcrumbs mb-4">
                    <ul>
                        <li><a href="{{ route('home') }}" x-target.push="category-content" class="link link-hover">Home</a></li>
                        <li><a href="{{ route('categories.index') }}" x-target.push="category-content" class="link link-hover">Categories</a></li>
                        @foreach($category->ancestors() as $ancestor)
                            <li><a href="{{ $ancestor->permalink() }}" x-target.push="category-content" class="link link-hover">{{ $ancestor->name }}</a></li>
                        @endforeach
                        <li class="text-base-content/60">{{ $category->name }}</li>
                    </ul>
                </nav>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h1 class="text-3xl font-bold">
                        <i class="ph ph-folder text-primary mr-2"></i>
                        {{ $category->name }}
                    </h1>
                    @auth
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost btn-sm gap-1">
                            <i class="ph ph-gear text-lg"></i>
                            Manage Categories
                        </a>
                    @endauth
                </div>

                @if($category->description)
                    <p class="text-base-content/70 text-lg max-w-2xl mt-2">{{ $category->description }}</p>
                @endif

                <p class="text-sm text-base-content/60 mt-2">
                    {{ $articleCount }} {{ Str::plural('article', $articleCount) }}
                    @if($photoCount > 0)
                        &middot; {{ $photoCount }} {{ Str::plural('photo', $photoCount) }}
                    @endif
                </p>
            </header>

            <x-category-feed
                :feedUrl="$category->permalink()"
                :children="$children"
                :articles="$articles"
                :photos="$photos"
                :currentType="$currentType"
                :articleCount="$articleCount"
                :photoCount="$photoCount"
                :searchPlaceholder="'Search in ' . $category->name . '...'"
            />

        </div>
    </div>

</x-layouts.public>
