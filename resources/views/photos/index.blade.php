<x-layouts.public title="Photos">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-6xl mx-auto">

        {{-- Breadcrumbs --}}
        <x-breadcrumb :items="[['label' => 'Photos']]" />

        {{-- Header --}}
        <header class="mb-6">
            <x-page-heading title="Photos" :subtitle="$subtitle" class="mb-2" />
        </header>

        {{-- Filter banner + Results (Alpine AJAX target) --}}
        <div id="photo-results" x-merge="morph">
            <x-filter-banner :action="route('photos.index')" target="photo-results" :clearRoute="route('photos.index')" persistKey="photos_filters_open">
                <x-slot:toolbar>
                    @auth
                        <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-1">
                            <i class="ph ph-gear text-lg"></i>
                            Manage Photos
                        </a>
                        <button class="btn btn-primary btn-sm gap-2"
                                onclick="document.getElementById('upload-photo-modal').showModal()">
                            <i class="ph ph-upload-simple"></i>
                            Upload
                        </button>
                    @endauth
                </x-slot:toolbar>

                <x-filter-banner.search placeholder="Search by alt text, slug, or caption..." :colspan="auth()->check() ? 1 : 2" />
                <x-filter-banner.category-select :categories="$categories" />
                <x-filter-banner.select name="status" label="Status"
                    :options="['public' => 'Public', 'private' => 'Private']"
                    emptyLabel="All" default="public" :auth="true" />
                <x-filter-banner.sort />
            </x-filter-banner>
            @if($photos->isNotEmpty())
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-1">
                    @foreach($photos as $photo)
                        <x-photo-card :photo="$photo" :author-name="$authorName" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $photos->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="text-6xl mb-4"><i class="ph ph-camera-slash text-base-content/30"></i></div>
                    @if(request('search') || request('category') || request('status') || request('sort'))
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

                        <input type="hidden" name="status" value="public">
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
