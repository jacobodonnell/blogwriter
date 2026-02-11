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
            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-title">Total Articles</div>
                <div class="stat-value">{{ $stats['total_articles'] ?? 0 }}</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-title">Published</div>
                <div class="stat-value text-success">{{ $stats['published'] ?? 0 }}</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-title">Drafts</div>
                <div class="stat-value text-warning">{{ $stats['drafts'] ?? 0 }}</div>
            </div>

            <div class="stat bg-base-100 shadow rounded-lg">
                <div class="stat-title">Categories</div>
                <div class="stat-value">{{ $stats['categories'] ?? 0 }}</div>
            </div>
        </div>

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
                                            'badge-ghost' => $article->status->value === 'hidden',
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
                                    <a href="{{ $article->permalink() }}" target="_blank" class="btn btn-sm btn-ghost" title="View on Site">
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

        {{-- Quick Actions --}}
        <div class="flex gap-4">
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="ph ph-plus text-xl mr-2"></i>
                New Article
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                <i class="ph ph-folder text-xl mr-2"></i>
                Manage Categories
            </a>
        </div>
    </div>
</x-layouts.admin>