<x-layouts.admin title="Photos">
    <x-slot:breadcrumb>
        <li>Photos</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Photos</h1>
                <p class="text-base-content/70 mt-1">Manage your photo library.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('photos.index') }}" class="btn btn-ghost">
                    <i class="ph ph-eye text-xl mr-2"></i>
                    View Photos
                </a>
                <a href="{{ route('admin.photos.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus text-xl mr-2"></i>
                    New Photo
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.photos.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="form-control w-full md:w-48">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                    @if(request('status'))
                        <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">
                            Clear Filters
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Photos Grid --}}
        @if($photos->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($photos as $photo)
                    <article class="card bg-base-100 shadow">
                        <figure class="relative aspect-square">
                            <img src="{{ $photo->image_url }}"
                                 alt="{{ $photo->alt_text }}"
                                 class="w-full h-full object-cover">

                            {{-- Status Badge --}}
                            <div class="absolute top-2 left-2">
                                <span @class([
                                    'badge',
                                    'badge-success' => $photo->status->value === 'published',
                                    'badge-warning' => $photo->status->value === 'draft',
                                ])>
                                    {{ $photo->status->label() }}
                                </span>
                            </div>
                        </figure>

                        <div class="card-body">
                            <h3 class="card-title text-base line-clamp-2">
                                {{ $photo->alt_text }}
                            </h3>

                            @if($photo->caption)
                                <p class="text-sm text-base-content/60 line-clamp-2">
                                    {{ Str::limit(strip_tags(Str::markdown($photo->caption)), 80) }}
                                </p>
                            @endif

                            {{-- Metadata --}}
                            <div class="text-sm text-base-content/60 mt-2">
                                <div class="flex items-center gap-2">
                                    <i class="ph ph-calendar-blank"></i>
                                    {{ $photo->published_at?->format('M j, Y') ?? 'Not published' }}
                                </div>
                                @if($photo->articles()->count() > 0)
                                    <div class="flex items-center gap-2 mt-1">
                                        <i class="ph ph-article"></i>
                                        Used in {{ $photo->articles()->count() }} {{ Str::plural('article', $photo->articles()->count()) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="card-actions justify-end mt-4">
                                <a href="{{ route('admin.photos.edit', $photo) }}"
                                   class="btn btn-sm btn-ghost"
                                   title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>

                                <a href="{{ route('admin.photos.show', $photo) }}"
                                   class="btn btn-sm btn-ghost"
                                   title="View details">
                                    <i class="ph ph-info text-lg"></i>
                                </a>

                                @if($photo->isPublic())
                                    <a href="{{ route('photos.show', $photo->slug) }}"
                                       class="btn btn-sm btn-ghost"
                                       title="View">
                                        <i class="ph ph-eye text-lg"></i>
                                    </a>
                                @endif

                                <form method="POST"
                                      action="{{ route('admin.photos.destroy', $photo) }}"
                                      class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this photo?{{ $photo->articles()->count() > 0 ? ' This photo is used in ' . $photo->articles()->count() . ' article(s).' : '' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-ghost text-error"
                                            title="Delete">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $photos->links() }}
            </div>
        @else
            <div class="text-center py-12 card bg-base-100 shadow">
                <div class="card-body">
                    <p class="text-base-content/60 mb-4">No photos found.</p>
                    @if(request('status'))
                        <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost">Clear Filters</a>
                    @else
                        <a href="{{ route('admin.photos.create') }}" class="btn btn-primary">Upload First Photo</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-layouts.admin>
