{{-- Table card: targeted by Alpine AJAX via #articles-table --}}
<div id="articles-table" class="card bg-base-100 shadow">
    <div class="card-body p-0">
        @if($articles->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th x-show="columns.featuredImage" x-cloak>
                                Image
                            </th>
                            <th x-show="columns.title">
                                @include('admin.articles._sort-header', ['column' => 'title', 'label' => 'Article'])
                            </th>
                            <th x-show="columns.status">
                                @include('admin.articles._sort-header', ['column' => 'status', 'label' => 'Status'])
                            </th>
                            <th x-show="columns.categories">
                                Category
                            </th>
                            <th x-show="columns.publishedAt" x-cloak>
                                @include('admin.articles._sort-header', ['column' => 'published_at', 'label' => 'Published'])
                            </th>
                            <th x-show="columns.createdAt" x-cloak>
                                @include('admin.articles._sort-header', ['column' => 'created_at', 'label' => 'Created'])
                            </th>
                            <th x-show="columns.updatedAt">
                                @include('admin.articles._sort-header', ['column' => 'updated_at', 'label' => 'Updated'])
                            </th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $placeholderUrl = placeholder_image_url(); @endphp
                        @foreach($articles as $article)
                            <tr>
                                <td x-show="columns.featuredImage" x-cloak class="w-20">
                                    @if($article->photo_id && $article->featuredPhoto)
                                        <a href="{{ route('admin.photos.edit', $article->featuredPhoto) }}" class="relative block w-20 h-14 rounded overflow-hidden bg-base-300">
                                            <img src="{{ $article->featuredPhoto->getFirstMediaUrl('image', 'thumbnail') }}"
                                                 alt=""
                                                 class="w-full h-full object-contain" />
                                            <span class="absolute bottom-1 right-1 bg-success/80 text-success-content rounded p-0.5 leading-none" title="Uploaded">
                                                <i class="ph ph-upload-simple text-xs"></i>
                                            </span>
                                        </a>
                                    @elseif($article->external_featured_img_url)
                                        <div class="relative w-20 h-14 rounded overflow-hidden bg-base-300">
                                            <img src="{{ $article->external_featured_img_url }}"
                                                 alt=""
                                                 class="w-full h-full object-contain" />
                                            <span class="absolute bottom-1 right-1 bg-info/80 text-info-content rounded p-0.5 leading-none" title="External">
                                                <i class="ph ph-arrow-square-out text-xs"></i>
                                            </span>
                                        </div>
                                    @else
                                        <div class="w-20 h-14 rounded bg-base-300 flex items-center justify-center" title="No featured image">
                                            <i class="ph ph-image text-xl text-base-content/30"></i>
                                        </div>
                                    @endif
                                </td>
                                <td x-show="columns.title">
                                    <div class="font-semibold">{{ $article->title }}</div>
                                    <div class="text-sm text-base-content/60">{{ Str::limit($article->slug, 40) }}</div>
                                </td>
                                <td x-show="columns.status">
                                    <span @class([
                                        'badge',
                                        'badge-success' => $article->status->value === 'published',
                                        'badge-warning' => $article->status->value === 'draft',
                                    ])>
                                        {{ $article->status->label() }}
                                    </span>
                                </td>
                                <td x-show="columns.categories">
                                    @if($article->category)
                                        <span class="badge badge-sm badge-outline">{{ $article->category->name }}</span>
                                    @else
                                        <span class="text-base-content/40 text-sm">—</span>
                                    @endif
                                </td>
                                <td x-show="columns.publishedAt" x-cloak class="text-sm text-base-content/60">
                                    {{ $article->published_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td x-show="columns.createdAt" x-cloak class="text-sm text-base-content/60">
                                    {{ $article->created_at->diffForHumans() }}
                                </td>
                                <td x-show="columns.updatedAt" class="text-sm text-base-content/60">
                                    {{ $article->updated_at->diffForHumans() }}
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button tooltip="Edit" href="{{ route('admin.articles.edit', $article) }}" icon="pencil-simple" />
                                        @if($article->isPublished())
                                            <x-admin.icon-button tooltip="View Published" href="{{ $article->permalink() }}" icon="eye" />
                                        @endif
                                        <button type="button"
                                                @click="deleteAction = '{{ route('admin.articles.destroy', $article) }}'; $refs.deleteModal.showModal()"
                                                class="btn btn-ghost btn-sm btn-square text-error"
                                                data-tip="Delete">
                                            <i class="ph ph-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-base-content/60 mb-4">No articles found.</p>
                @if(request('category') || request('status') || request('search'))
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">Clear Filters</a>
                @else
                    <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">Create First Article</a>
                @endif
            </div>
        @endif
    </div>
</div>
