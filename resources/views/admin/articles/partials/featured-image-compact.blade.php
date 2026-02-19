@php
    $featuredPhoto = $article->exists ? $article->featuredPhoto : null;
    $photoMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->image_url])->toArray();
    $captionMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->caption])->toArray();
@endphp

<div x-data="{
        photoUrls: @js($photoMap),
        photoCaptions: @js($captionMap),
        get previewUrl() {
            if (this.uploadedPhotoUrl) return this.uploadedPhotoUrl;
            if (this.selectedPhotoId && this.photoUrls[this.selectedPhotoId]) return this.photoUrls[this.selectedPhotoId];
            return null;
        }
     }">
    <input type="hidden" name="photo_id" :value="selectedPhotoId">

    {{-- Photo Select --}}
    <select x-model="selectedPhotoId" data-test="photo-select"
            @change="if (selectedPhotoId) { featuredImageUrl = ''; uploadedPhotoUrl = null; hasNewPhoto = false; usePhotoCaption = false; featuredImageCaption = ''; const fi = document.getElementById('featured-image-file-input'); if (fi) fi.value = ''; }"
            class="select select-bordered select-sm w-full">
        <option value="">No featured image</option>
        @foreach($photos as $photo)
            <option value="{{ $photo->id }}">
                {{ $photo->alt_text }}
            </option>
        @endforeach
    </select>

    <div class="flex gap-2 mt-2">
        {{-- Upload New Button --}}
        <button type="button"
                @click="document.getElementById('upload-photo-modal').showModal()"
                data-test="upload-new-photo"
                class="btn btn-ghost btn-sm flex-1 gap-2">
            <i class="ph ph-upload-simple"></i>
            Upload New
        </button>

        {{-- External URL Toggle --}}
        <button type="button"
                @click="showUrlField = !showUrlField; if (showUrlField) $nextTick(() => $refs.urlField.focus())"
                class="btn btn-ghost btn-sm flex-1 gap-2"
                :class="{ 'btn-active': featuredImageUrl }">
            <i class="ph ph-link"></i>
            URL
        </button>
    </div>

    {{-- External URL Input --}}
    <div x-show="showUrlField"
         x-transition
         class="mt-2"
         x-cloak>
        <input x-ref="urlField" type="url" name="featured_image"
               x-model="featuredImageUrl"
               class="input input-bordered input-sm w-full"
               placeholder="https://example.com/image.jpg"
               @input="if (featuredImageUrl) { selectedPhotoId = ''; uploadedPhotoUrl = null; hasNewPhoto = false; usePhotoCaption = false; const fi = document.getElementById('featured-image-file-input'); if (fi) fi.value = ''; }">
        <p class="text-xs text-base-content/50 mt-1">External URL overrides photo selection.</p>
    </div>

    {{-- Image Preview (Alpine-driven) --}}
    <template x-if="previewUrl || featuredImageUrl">
        <div class="mt-3">
            <p class="text-xs font-medium mb-1" x-text="uploadedPhotoUrl ? 'New upload:' : 'Preview:'"></p>
            <img :src="previewUrl || featuredImageUrl"
                 alt="Featured image preview"
                 class="w-full max-h-32 object-contain rounded-lg opacity-0 transition-opacity duration-300"
                 @load="$el.classList.remove('opacity-0')">
        </div>
    </template>

    {{-- Fallback: server-rendered current image when no Alpine preview --}}
    @if($article->featured_image_url)
        <div class="mt-3" x-show="!previewUrl && !featuredImageUrl">
            <p class="text-xs font-medium mb-1">Current:</p>
            <img src="{{ $article->featured_image_url }}"
                 alt="{{ $featuredPhoto?->alt_text ?? 'Featured image' }}"
                 class="w-full max-h-32 object-contain rounded-lg">
        </div>
    @endif

    {{-- Caption Section --}}
    <template x-if="previewUrl || featuredImageUrl">
        <div class="mt-3 space-y-2">
            {{-- Use Photo Caption toggle (only when a photo is selected, not URL/upload) --}}
            <div x-show="selectedPhotoId && !uploadedPhotoUrl && !featuredImageUrl" x-cloak>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" class="toggle toggle-sm" x-model="usePhotoCaption">
                    <span class="text-xs">Use photo's caption</span>
                </label>
            </div>

            {{-- Photo caption preview (read-only) --}}
            <div x-show="usePhotoCaption && selectedPhotoId && photoCaptions[selectedPhotoId]" x-cloak>
                <div class="bg-base-200 rounded-lg px-3 py-2 text-sm text-base-content/70"
                     x-text="photoCaptions[selectedPhotoId]"></div>
            </div>

            {{-- Custom caption textarea --}}
            <div x-show="!usePhotoCaption || !selectedPhotoId" x-cloak>
                <textarea x-model="featuredImageCaption"
                          class="textarea textarea-bordered textarea-sm w-full h-16 text-sm"
                          placeholder="Image caption (optional)"></textarea>
            </div>
        </div>
    </template>
</div>
