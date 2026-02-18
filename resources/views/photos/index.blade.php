<x-layouts.public title="Photos - {{ config('app.name') }}">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-6xl mx-auto">

        {{-- Breadcrumbs --}}
        <nav class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('home') }}" class="link link-hover">Home</a></li>
                <li class="text-base-content/60">Photos</li>
            </ul>
        </nav>

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="min-w-0">
                <h1 class="text-4xl font-bold mb-1">Photos</h1>
                @if($subtitle)
                    <p class="text-base-content/60">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="flex shrink-0 gap-2">
                @auth
                    <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-gear text-lg"></i>
                        Manage
                    </a>
                    <button class="btn btn-primary btn-sm gap-2"
                            onclick="document.getElementById('upload-photo-modal').showModal()">
                        <i class="ph ph-upload-simple"></i>
                        Upload
                    </button>
                @endauth
            </div>
        </header>

        {{-- Filter banner + Results (Alpine AJAX target) --}}
        <div id="photo-results" x-merge="morph">
            <x-filter-banner :action="route('photos.index')" target="photo-results" :clearRoute="route('photos.index')">
                <div class="sm:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search photos..."
                           class="input input-bordered w-full"
                           @input.debounce.400ms="$el.form.requestSubmit()">
                </div>
                <div class="@auth sm:col-span-1 @else sm:col-span-2 @endauth">
                    <x-category-select :categories="$categories"
                        name="category" emptyLabel="All Categories"
                        :selected="request('category')" :useSlug="true"
                        @change="$el.form.requestSubmit()" />
                </div>
                @auth
                    <div class="sm:col-span-1">
                        <select name="status" class="select select-bordered w-full"
                                @change="$el.form.requestSubmit()">
                            <option value="">All Status</option>
                            <option value="published" @selected(request('status') === 'published')>Published</option>
                            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        </select>
                    </div>
                @endauth
            </x-filter-banner>
            @if($photos->count() > 0)
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1">
                    @foreach($photos as $photo)
                        {{-- h-entry for each photo --}}
                        <article class="h-entry relative group">
                            <a href="{{ route('photos.show', $photo->slug) }}"
                               class="block aspect-square overflow-hidden">
                                <img src="{{ $photo->image_url }}"
                                     alt="{{ $photo->alt_text }}"
                                     class="u-photo w-full h-full object-cover group-hover:brightness-75 transition-all duration-200">
                            </a>

                            {{-- Auth overlays --}}
                            @auth
                                @if($photo->status === \App\Enums\Status::Draft)
                                    <span class="absolute top-2 left-2 badge badge-warning badge-sm">Draft</span>
                                @endif
                                <a href="{{ route('admin.photos.edit', $photo) }}"
                                   class="absolute top-2 right-2 btn btn-circle btn-xs btn-ghost bg-base-100/80 opacity-0 group-hover:opacity-100 transition-opacity"
                                   title="Edit photo">
                                    <i class="ph ph-pencil-simple text-sm"></i>
                                </a>
                            @endauth

                            {{-- Hidden microformat data --}}
                            <span class="hidden">
                                <span class="p-name">{{ $photo->alt_text }}</span>
                                <time class="dt-published" datetime="{{ $photo->published_at?->toIso8601String() }}">{{ $photo->published_at?->format('F j, Y') }}</time>
                                <a class="u-url" href="{{ route('photos.show', $photo->slug) }}">Permalink</a>
                                <span class="p-author h-card"><span class="p-name">{{ $authorName }}</span></span>
                            </span>
                        </article>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $photos->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl mb-4"><i class="ph ph-camera-slash text-base-content/30"></i></div>
                    @if(request('search') || request('category') || request('status'))
                        <h2 class="text-2xl font-bold mb-2">No photos found</h2>
                        <p class="text-base-content/60 mb-6">Try adjusting your filters.</p>
                        <a href="{{ route('photos.index') }}" class="btn btn-ghost">Clear Filters</a>
                    @else
                        <h2 class="text-2xl font-bold mb-2">No photos yet</h2>
                        <p class="text-base-content/60">Check back soon for new photos.</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Upload Modal (auth only) --}}
        @auth
            <x-editor-modal id="upload-photo-modal" title="Upload Photo" maxWidth="max-w-xl">
                <form id="frontend-photo-upload-form"
                      method="POST"
                      action="{{ route('admin.photos.store') }}"
                      enctype="multipart/form-data"
                      x-data="{ uploadPreview: null }">
                    @csrf

                    <div class="space-y-3">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Image</legend>
                            <input type="file" name="image_file"
                                   class="file-input file-input-bordered w-full"
                                   accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                                   required
                                   @change="if ($event.target.files[0]) { uploadPreview = URL.createObjectURL($event.target.files[0]); }">
                            <img x-show="uploadPreview" :src="uploadPreview"
                                 class="w-full max-h-40 object-cover rounded-lg mt-2"
                                 alt="Upload preview"
                                 x-cloak>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Alt Text</legend>
                            <input type="text" name="alt_text"
                                   class="input input-bordered w-full"
                                   placeholder="Describe the image for accessibility"
                                   required>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Caption (optional)</legend>
                            <textarea name="caption"
                                      class="textarea textarea-bordered w-full h-16 text-sm"
                                      placeholder="Photo caption"></textarea>
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Category (optional)</legend>
                            <x-category-select :categories="$categories ?? collect()" />
                        </fieldset>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Date Taken (optional)</legend>
                            <input type="date" name="taken_at"
                                   class="input input-bordered w-full">
                        </fieldset>

                        <input type="hidden" name="status" value="published">
                    </div>
                </form>

                <x-slot:actions>
                    <button type="submit" form="frontend-photo-upload-form" class="btn btn-primary">
                        Upload Photo
                    </button>
                </x-slot:actions>
            </x-editor-modal>
        @endauth
    </div>

</x-layouts.public>
