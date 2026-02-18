<x-layouts.public title="Photos - {{ config('app.name') }}">

    {{-- h-feed for IndieWeb --}}
    <div class="h-feed max-w-6xl mx-auto" x-data="{ uploading: false }">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="min-w-0">
                <h1 class="text-4xl font-bold mb-1">Photos</h1>
                @if($subtitle)
                    <p class="text-base-content/60">{{ $subtitle }}</p>
                @endif
            </div>
            @auth
                <div class="flex shrink-0 gap-2">
                    <a href="{{ route('admin.photos.index') }}" class="btn btn-ghost btn-sm gap-1">
                        <i class="ph ph-gear text-lg"></i>
                        Manage
                    </a>
                    <button class="btn btn-primary btn-sm gap-2"
                            onclick="document.getElementById('upload-photo-modal').showModal()">
                        <i class="ph ph-upload-simple"></i>
                        Upload
                    </button>
                </div>
            @endauth
        </header>

        {{-- IG-Style Grid --}}
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

                        {{-- Auth edit overlay --}}
                        @auth
                            <a href="{{ route('admin.photos.edit', $photo) }}"
                               class="absolute top-2 right-2 btn btn-circle btn-xs btn-ghost bg-base-100/80 opacity-0 group-hover:opacity-100 transition-opacity"
                               title="Edit photo">
                                <i class="ph ph-pencil-simple text-sm"></i>
                            </a>
                        @endauth

                        {{-- Hidden microformat data --}}
                        <span class="hidden">
                            <span class="p-name">{{ $photo->alt_text }}</span>
                            <time class="dt-published" datetime="{{ $photo->published_at->toIso8601String() }}">{{ $photo->published_at->format('F j, Y') }}</time>
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
                <div class="text-6xl mb-4">📷</div>
                <h2 class="text-2xl font-bold mb-2">No photos yet</h2>
                <p class="text-base-content/60">Check back soon for new photos.</p>
            </div>
        @endif

        {{-- Upload Modal (auth only) --}}
        @auth
            <x-editor-modal id="upload-photo-modal" title="Upload Photo" maxWidth="max-w-xl">
                <form id="frontend-photo-upload-form"
                      method="POST"
                      action="{{ route('admin.photos.store') }}"
                      enctype="multipart/form-data"
                      x-data="{ uploadPreview: null }"
                      @submit.prevent="
                          uploading = true;
                          const form = document.getElementById('frontend-photo-upload-form');
                          const formData = new FormData(form);

                          fetch(form.action, {
                              method: 'POST',
                              headers: { 'X-Requested-With': 'XMLHttpRequest' },
                              body: formData,
                          })
                          .then(r => {
                              if (!r.ok) throw r;
                              return r.json();
                          })
                          .then(data => {
                              document.getElementById('upload-photo-modal').close();
                              uploading = false;
                              form.reset();
                              uploadPreview = null;
                              window.location.reload();
                          })
                          .catch(() => { uploading = false; })
                      ">
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
                            <select name="category_id" class="select select-bordered w-full">
                                <option value="">No Category</option>
                                @forelse($categories ?? [] as $rootCat)
                                    <option value="{{ $rootCat->id }}">{{ $rootCat->name }}</option>
                                    @foreach($rootCat->children as $childCat)
                                        <option value="{{ $childCat->id }}">&nbsp;&nbsp;└ {{ $childCat->name }}</option>
                                    @endforeach
                                @empty
                                @endforelse
                            </select>
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
                    <button type="submit" form="frontend-photo-upload-form" class="btn btn-primary" :disabled="uploading">
                        <span x-show="!uploading">Upload Photo</span>
                        <span x-show="uploading" class="loading loading-spinner loading-sm" x-cloak></span>
                    </button>
                </x-slot:actions>
            </x-editor-modal>
        @endauth
    </div>

</x-layouts.public>
