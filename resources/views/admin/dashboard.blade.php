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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
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
                                            'badge-success' => $article->status === 'published',
                                            'badge-warning' => $article->status === 'draft',
                                            'badge-ghost' => $article->status === 'hidden',
                                        ])>
                                            {{ ucfirst($article->status) }}
                                        </span>
                                        <span>{{ $article->updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-ghost" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <a href="{{ $article->permalink() }}" target="_blank" class="btn btn-sm btn-ghost" title="View on Site">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Article
            </a>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Manage Categories
            </a>
        </div>
    </div>
</x-layouts.admin>