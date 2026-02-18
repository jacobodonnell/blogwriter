<x-layouts.public :title="$category->name . ' - ' . config('app.name')">

    <x-slot:seo>
        <x-seo-meta :title="$category->name . ' - ' . config('app.name')" :description="$category->description" />
    </x-slot:seo>

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-4xl mx-auto">

        {{-- Category Header --}}
        <header class="mb-8">
            <nav class="text-sm breadcrumbs mb-4">
                <ul>
                    <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                    <li><a href="{{ route('categories.index') }}" class="link link-hover">Categories</a></li>
                    @foreach($category->ancestors() as $ancestor)
                        <li><a href="{{ $ancestor->permalink() }}" class="link link-hover">{{ $ancestor->name }}</a></li>
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

            @php
                $totalArticles = auth()->check()
                    ? $category->articles()->count()
                    : $category->articles()->where('status', \App\Enums\Status::Published)->count();
                $totalPhotos = auth()->check()
                    ? $category->photos()->count()
                    : $category->photos()->where('status', \App\Enums\Status::Published)->count();
            @endphp

            <p class="text-sm text-base-content/60 mt-2">
                {{ $totalArticles }} {{ Str::plural('article', $totalArticles) }}
                @if($totalPhotos > 0)
                    &middot; {{ $totalPhotos }} {{ Str::plural('photo', $totalPhotos) }}
                @endif
            </p>

            @if($children->isNotEmpty())
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($children as $child)
                        <a href="{{ $child->permalink() }}" class="btn btn-sm btn-outline gap-1">
                            <i class="ph ph-folder text-sm"></i>
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </header>

        {{-- Content-Type Filter Tabs --}}
        <form action="{{ $category->permalink() }}" method="GET" x-target="category-content" class="mb-6">
            <div class="flex gap-1">
                <button type="submit" name="type" value="all"
                        class="btn btn-sm {{ $currentType === 'all' ? 'btn-primary' : 'btn-ghost' }}">
                    All
                </button>
                <button type="submit" name="type" value="articles"
                        class="btn btn-sm {{ $currentType === 'articles' ? 'btn-primary' : 'btn-ghost' }}">
                    <i class="ph ph-article"></i>
                    Articles
                </button>
                <button type="submit" name="type" value="photos"
                        class="btn btn-sm {{ $currentType === 'photos' ? 'btn-primary' : 'btn-ghost' }}">
                    <i class="ph ph-camera"></i>
                    Photos
                </button>
            </div>
        </form>

        {{-- Content (articles + photos, targeted by Alpine AJAX) --}}
        @include('public.category._content')

    </div>

</x-layouts.public>
