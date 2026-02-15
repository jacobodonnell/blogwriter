@php
    $featuredPhoto = $article->exists ? $article->featuredPhoto : null;
    $photoMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->image_url])->toArray();
@endphp

<div x-data="{
        photoUrls: @js($photoMap),
        get previewUrl() {
            if (this.uploadedPhotoUrl) return this.uploadedPhotoUrl;
            if (this.selectedPhotoId && this.photoUrls[this.selectedPhotoId]) return this.photoUrls[this.selectedPhotoId];
            return null;
        }
     }">
    <input type="hidden" name="photo_id" :value="selectedPhotoId">

    {{-- Photo Select --}}
    <select x-model="selectedPhotoId"
            @change="if (selectedPhotoId) { featuredImageUrl = ''; uploadedPhotoUrl = null; const fi = document.getElementById('featured-image-file-input'); if (fi) fi.value = ''; }"
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
               @input="if (featuredImageUrl) { selectedPhotoId = ''; uploadedPhotoUrl = null; const fi = document.getElementById('featured-image-file-input'); if (fi) fi.value = ''; }">
        <p class="text-xs text-base-content/50 mt-1">External URL overrides photo selection.</p>
    </div>

    {{-- Image Preview (Alpine-driven) --}}
    <template x-if="previewUrl || featuredImageUrl">
        <div class="mt-3">
            <p class="text-xs font-medium mb-1" x-text="uploadedPhotoUrl ? 'New upload:' : 'Preview:'"></p>
            <img :src="previewUrl || featuredImageUrl"
                 alt="Featured image preview"
                 class="w-full max-h-32 object-cover rounded-lg opacity-0 transition-opacity duration-300"
                 @load="$el.classList.remove('opacity-0')">
        </div>
    </template>

    {{-- Fallback: server-rendered current image when no Alpine preview --}}
    @if($article->featured_image_url)
        <div class="mt-3" x-show="!previewUrl && !featuredImageUrl">
            <p class="text-xs font-medium mb-1">Current:</p>
            <img src="{{ $article->featured_image_url }}"
                 alt="{{ $featuredPhoto?->alt_text ?? 'Featured image' }}"
                 class="w-full max-h-32 object-cover rounded-lg">
        </div>
    @endif
</div>
