<x-layouts.admin title="Dashboard">
    <x-slot:breadcrumb>{{-- Intentional: renders breadcrumb bar with just the root "Dashboard" link --}}</x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-base-content/70 mt-1">Welcome to your BlogWriter admin panel.</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label' => 'Total Articles', 'value' => $stats['total_articles'], 'route' => route('admin.articles.index'), 'icon' => 'article', 'color' => 'primary'],
                    ['label' => 'Published Articles', 'value' => $stats['published_articles'], 'route' => route('admin.articles.index', ['status' => 'published']), 'icon' => 'check-circle', 'color' => 'success', 'valueColor' => 'text-success'],
                    ['label' => 'Draft Articles', 'value' => $stats['draft_articles'], 'route' => route('admin.articles.index', ['status' => 'draft']), 'icon' => 'pencil-line', 'color' => 'warning', 'valueColor' => 'text-warning'],
                    ['label' => 'Categories', 'value' => $stats['categories'], 'route' => route('admin.categories.index'), 'icon' => 'folder', 'color' => 'secondary'],
                    ['label' => 'Total Photos', 'value' => $stats['total_photos'], 'route' => route('admin.photos.index'), 'icon' => 'image', 'color' => 'info'],
                    ['label' => 'Published Photos', 'value' => $stats['published_photos'], 'route' => route('admin.photos.index', ['status' => 'published']), 'icon' => 'image-square', 'color' => 'success', 'valueColor' => 'text-success'],
                    ['label' => 'Draft Photos', 'value' => $stats['draft_photos'], 'route' => route('admin.photos.index', ['status' => 'draft']), 'icon' => 'image-broken', 'color' => 'warning', 'valueColor' => 'text-warning'],
                ];
            @endphp

            @foreach ($cards as $card)
                <a href="{{ $card['route'] }}" class="card bg-base-100 shadow hover:shadow-md hover:bg-base-200 transition">
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-base-content/60">{{ $card['label'] }}</p>
                                <p class="text-3xl font-bold {{ $card['valueColor'] ?? '' }}">{{ $card['value'] }}</p>
                            </div>
                            <i class="ph ph-{{ $card['icon'] }} text-3xl text-{{ $card['color'] }}"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Recent Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Articles --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="card-title">Recent Articles</h2>
                        <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-ghost">
                            View All
                            <i class="ph ph-caret-right text-lg ml-1"></i>
                        </a>
                    </div>

                    @if($recentArticles->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentArticles as $article)
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold truncate">{{ $article->title }}</h3>
                                        <div class="flex items-center gap-2 mt-1 text-sm text-base-content/60">
                                            <span @class([
                                                'badge badge-sm',
                                                'badge-success' => $article->status->value === 'published',
                                                'badge-warning' => $article->status->value === 'draft',
                                            ])>
                                                {{ $article->status->label() }}
                                            </span>
                                            <span>{{ $article->updated_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <x-admin.icon-button tooltip="Edit" href="{{ route('admin.articles.edit', $article) }}" icon="pencil-simple" />
                                        @if($article->isPublished())
                                            <x-admin.icon-button tooltip="View on Site" href="{{ $article->permalink() }}" icon="eye" />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/60">
                            <p>No articles yet.</p>
                            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary mt-4">Create First Article</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Photos --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="card-title">Recent Photos</h2>
                        <a href="{{ route('admin.photos.index') }}" class="btn btn-sm btn-ghost">
                            View All
                            <i class="ph ph-caret-right text-lg ml-1"></i>
                        </a>
                    </div>

                    @if($recentPhotos->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($recentPhotos as $photo)
                                <div class="card bg-base-200">
                                    @if($photo->thumbnail_url)
                                        <figure class="px-4 pt-4">
                                            <img
                                                src="{{ $photo->thumbnail_url }}"
                                                alt="{{ $photo->caption ?? 'Photo' }}"
                                                class="rounded-lg w-full h-48 object-contain bg-base-300"
                                            >
                                        </figure>
                                    @else
                                        <figure class="px-4 pt-4">
                                            <div class="rounded-lg w-full h-48 bg-base-300 flex items-center justify-center">
                                                <i class="ph ph-image text-4xl text-base-content/40"></i>
                                            </div>
                                        </figure>
                                    @endif
                                    <div class="card-body p-4">
                                        <h3 class="card-title text-sm truncate">{{ $photo->caption ?? 'Untitled Photo' }}</h3>
                                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                                            <span @class([
                                                'badge badge-sm',
                                                'badge-success' => $photo->status->value === 'published',
                                                'badge-warning' => $photo->status->value === 'draft',
                                            ])>
                                                {{ $photo->status->label() }}
                                            </span>
                                            <span>{{ $photo->updated_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="card-actions justify-end mt-2">
                                            <x-admin.icon-button tooltip="Edit" href="{{ route('admin.photos.edit', $photo) }}" icon="pencil-simple" />
                                            @if($photo->isPublic())
                                                <x-admin.icon-button tooltip="View on Site" href="{{ route('photos.show', $photo->slug) }}" icon="eye" />
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-base-content/60">
                            <p>No photos yet.</p>
                            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary mt-4">Upload First Photo</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:gap-4">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary btn-block sm:w-auto">
                <i class="ph ph-plus text-xl mr-2"></i>
                New Article
            </a>
            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary btn-block sm:w-auto">
                <i class="ph ph-camera text-xl mr-2"></i>
                Upload Photo
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-block sm:w-auto">
                <i class="ph ph-folder text-xl mr-2"></i>
                Manage Categories
            </a>
        </div>
    </div>
</x-layouts.admin>
