<x-layouts.public>

    <x-slot:seo>
        <x-seo-meta />
    </x-slot:seo>

    <div class="max-w-6xl mx-auto">

    <x-breadcrumb />

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_18rem] xl:grid-cols-[1fr_20rem] gap-8">

        {{-- Main: Articles h-feed --}}
        <div class="h-feed">

            {{-- Feed Header --}}
            <header class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div class="min-w-0">
                    <x-page-heading title="Recent Articles" :subtitle="$subtitle" class="mb-2" />
                </div>
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('articles.index') }}" class="btn btn-ghost btn-sm gap-1">
                        View All
                        <i class="ph ph-arrow-right"></i>
                    </a>
                    @auth
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-sm gap-1">
                            <i class="ph ph-plus"></i>
                            New Article
                        </a>
                    @endauth
                </div>
            </header>

            {{-- Articles Bento Grid --}}
            @if($articles->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($articles as $article)
                        <x-article-card :article="$article"
                            :featured="$loop->first" :stacked="!$loop->first"
                            :author-name="$authorName" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $articles->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">📝</div>
                    <h2 class="text-2xl font-bold mb-2">No articles yet</h2>
                    <p class="text-base-content/60 mb-6">Start writing your first article in the admin panel.</p>
                    @auth
                        <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                            <i class="ph ph-plus"></i>
                            Write Article
                        </a>
                    @endauth
                </div>
            @endif
        </div>

        {{-- Sidebar: Photos --}}
        <aside>
            <div class="h-feed">
                {{-- Sidebar Header --}}
                <header class="flex flex-wrap items-center justify-between gap-2 mb-4">
                    <h2 class="text-lg font-bold">Photos</h2>
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('photos.index') }}" class="btn btn-ghost btn-xs gap-1">
                            View All
                            <i class="ph ph-arrow-right text-sm"></i>
                        </a>
                        @auth
                            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary btn-xs gap-1">
                                <i class="ph ph-plus text-sm"></i>
                                Upload
                            </a>
                        @endauth
                    </div>
                </header>

                {{-- 3x3 Photo Grid --}}
                @if($photos->isNotEmpty())
                    <div class="flex flex-col gap-1 rounded-lg overflow-hidden">
                        @foreach($photos as $photo)
                            <x-photo-card :photo="$photo" :author-name="$authorName" :show-auth-overlays="false" />
                        @endforeach
                    </div>

                @else
                    <div class="text-center py-8 text-base-content/60">
                        <i class="ph ph-camera text-3xl mb-2"></i>
                        <p class="text-sm">No photos yet</p>
                    </div>
                @endif
            </div>
        </aside>

    </div>

    </div>

</x-layouts.public>
