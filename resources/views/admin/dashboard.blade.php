<x-layouts.admin>
    @section('title', 'Dashboard')

    <div class="space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Welcome to your BlogWriter admin panel.</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.articles.index') }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-primary">
                    <i class="ph ph-article text-3xl"></i>
                </div>
                <div class="stat-title">Total Articles</div>
                <div class="stat-value">{{ $stats['total_articles'] }}</div>
            </a>

            <a href="{{ route('admin.articles.index', ['status' => 'published']) }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-success">
                    <i class="ph ph-check-circle text-3xl"></i>
                </div>
                <div class="stat-title">Published Articles</div>
                <div class="stat-value text-success">{{ $stats['published_articles'] }}</div>
            </a>

            <a href="{{ route('admin.articles.index', ['status' => 'draft']) }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-warning">
                    <i class="ph ph-pencil-line text-3xl"></i>
                </div>
                <div class="stat-title">Draft Articles</div>
                <div class="stat-value text-warning">{{ $stats['draft_articles'] }}</div>
            </a>

            <a href="{{ route('admin.categories.index') }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-secondary">
                    <i class="ph ph-folder text-3xl"></i>
                </div>
                <div class="stat-title">Categories</div>
                <div class="stat-value">{{ $stats['categories'] }}</div>
            </a>

            <a href="{{ route('admin.photos.index') }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-info">
                    <i class="ph ph-image text-3xl"></i>
                </div>
                <div class="stat-title">Total Photos</div>
                <div class="stat-value">{{ $stats['total_photos'] }}</div>
            </a>

            <a href="{{ route('admin.photos.index', ['status' => 'published']) }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-success">
                    <i class="ph ph-image-square text-3xl"></i>
                </div>
                <div class="stat-title">Published Photos</div>
                <div class="stat-value text-success">{{ $stats['published_photos'] }}</div>
            </a>

            <a href="{{ route('admin.photos.index', ['status' => 'draft']) }}" class="stat bg-base-100 shadow rounded-lg hover:shadow-md hover:bg-base-200 transition">
                <div class="stat-figure text-warning">
                    <i class="ph ph-image-broken text-3xl"></i>
                </div>
                <div class="stat-title">Draft Photos</div>
                <div class="stat-value text-warning">{{ $stats['draft_photos'] }}</div>
            </a>
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
                                        <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
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
                                        <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-ghost" title="Edit">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                        </a>
                                        <a href="{{ $article->permalink() }}" class="btn btn-sm btn-ghost" title="View on Site">
                                            <i class="ph ph-eye text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
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
                        <div class="space-y-4">
                            @foreach($recentPhotos as $photo)
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        @if($photo->getFirstMediaUrl('image', 'thumbnail'))
                                            <img
                                                src="{{ $photo->getFirstMediaUrl('image', 'thumbnail') }}"
                                                alt="{{ $photo->caption ?? 'Photo' }}"
                                                class="w-12 h-12 rounded-lg object-cover shrink-0"
                                            >
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-base-300 flex items-center justify-center shrink-0">
                                                <i class="ph ph-image text-xl text-gray-400"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <h3 class="font-semibold truncate">{{ $photo->caption ?? 'Untitled Photo' }}</h3>
                                            <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                                                <span @class([
                                                    'badge badge-sm',
                                                    'badge-success' => $photo->status->value === 'published',
                                                    'badge-warning' => $photo->status->value === 'draft',
                                                ])>
                                                    {{ $photo->status->label() }}
                                                </span>
                                                <span>{{ $photo->updated_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        <a href="{{ route('admin.photos.edit', $photo) }}" class="btn btn-sm btn-ghost" title="Edit">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                        </a>
                                        <a href="{{ route('admin.photos.show', $photo) }}" class="btn btn-sm btn-ghost" title="View">
                                            <i class="ph ph-eye text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <p>No photos yet.</p>
                            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary mt-4">Upload First Photo</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex gap-4">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="ph ph-plus text-xl mr-2"></i>
                New Article
            </a>
            <a href="{{ route('admin.photos.create') }}" class="btn btn-primary">
                <i class="ph ph-camera text-xl mr-2"></i>
                Upload Photo
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                <i class="ph ph-folder text-xl mr-2"></i>
                Manage Categories
            </a>
        </div>
    </div>
</x-layouts.admin>
