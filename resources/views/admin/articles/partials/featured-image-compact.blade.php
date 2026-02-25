@php
    $featuredPhoto = $article->exists ? $article->featuredPhoto : null;
    $photoMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->image_url])->toArray();
    $captionMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->caption])->toArray();
    $labelMap = $photos->mapWithKeys(fn ($p) => [$p->id => $p->alt_text ?: 'Photo #' . $p->id])->toArray();
@endphp

<div x-data="featuredImage({ photoUrls: @js($photoMap), photoCaptions: @js($captionMap), photoLabels: @js($labelMap) })">
    <input type="hidden" name="photo_id" :value="selectedPhotoId">

    {{-- Photo Select — custom thumbnail dropdown --}}
    <div class="dropdown w-full" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                data-test="photo-select-trigger"
                class="btn btn-bordered btn-sm w-full justify-start gap-2 text-left">
            <template x-if="selectedPhotoId && photoUrls[selectedPhotoId]">
                <img :src="photoUrls[selectedPhotoId]" class="w-6 h-6 rounded object-cover shrink-0" alt="">
            </template>
            <span class="truncate flex-1"
                  x-text="selectedPhotoId && photoLabels[selectedPhotoId] ? photoLabels[selectedPhotoId] : (featuredImageUrl ? 'Using external URL' : 'No featured image')"></span>
            <i class="ph ph-caret-down ml-auto shrink-0"></i>
        </button>
        <ul x-show="open" @click.away="open = false" x-cloak
            class="absolute z-20 mt-1 w-full bg-base-100 rounded-box shadow-lg border border-base-300 max-h-52 overflow-y-auto p-1 space-y-0.5">
            <li>
                <button type="button"
                        data-test="photo-option-none"
                        class="w-full text-left px-2 py-1.5 text-sm rounded hover:bg-base-200 flex items-center gap-2"
                        @click="selectedPhotoId = ''; open = false; selectPhoto(); checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))">
                    <span class="w-8 h-8 rounded bg-base-300 flex items-center justify-center shrink-0">
                        <i class="ph ph-image text-base-content/40"></i>
                    </span>
                    No featured image
                </button>
            </li>
            @foreach($photos as $photo)
            <li>
                <button type="button"
                        data-test="photo-option-{{ $photo->id }}"
                        class="w-full text-left px-2 py-1.5 text-sm rounded hover:bg-base-200 flex items-center gap-2"
                        @click="selectedPhotoId = '{{ $photo->id }}'; open = false; selectPhoto(); checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))">
                    @if($photo->thumbnail_url)
                        <img src="{{ $photo->thumbnail_url }}" class="w-8 h-8 rounded object-cover shrink-0" alt="">
                    @else
                        <span class="w-8 h-8 rounded bg-base-300 flex items-center justify-center shrink-0">
                            <i class="ph ph-image text-base-content/40"></i>
                        </span>
                    @endif
                    <span class="truncate">{{ $photo->alt_text ?: 'Photo #' . $photo->id }}</span>
                </button>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="flex gap-2 mt-2">
        {{-- Upload New Button --}}
        <button type="button"
                @click="$refs.uploadPhotoModal.showModal()"
                data-test="upload-new-photo"
                class="btn btn-ghost btn-sm flex-1 gap-2">
            <i class="ph ph-upload-simple"></i>
            Upload New
        </button>

        {{-- External URL Toggle --}}
        <button type="button"
                @click="showUrlField = !showUrlField; if (showUrlField) $nextTick(() => $refs.urlField.focus())"
                data-test="url-toggle"
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
               data-test="featured-image-url"
               class="input input-bordered input-sm w-full"
               placeholder="https://example.com/image.jpg"
               @input="setExternalUrl(); checkDirty()">
        <p class="text-xs text-base-content/50 mt-1">External URL overrides photo selection.</p>
    </div>

    {{-- Remove External URL Button --}}
    <button type="button"
            x-show="featuredImageUrl"
            x-cloak
            @click="removeExternalUrl(); checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))"
            data-test="remove-external-url"
            class="btn btn-ghost btn-sm mt-2 gap-1 text-error w-full justify-start">
        <i class="ph ph-x-circle"></i>
        Remove image URL
    </button>

    {{-- Image Preview (Alpine-driven) --}}
    <template x-if="previewUrl || featuredImageUrl">
        <div class="mt-3">
            <p class="text-xs font-medium mb-1" x-text="uploadedPhotoUrl ? 'New upload:' : 'Preview:'"></p>
            <img :src="previewUrl || featuredImageUrl"
                 alt="Featured image preview"
                 class="w-full max-h-64 object-contain rounded-lg opacity-0 transition-opacity duration-300"
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
                    <input type="checkbox" id="use-photo-caption" class="toggle toggle-sm" x-model="usePhotoCaption" @change="checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))">
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
                <textarea id="featured-image-caption" x-model="featuredImageCaption"
                          @input="checkDirty()"
                          class="textarea textarea-bordered textarea-sm w-full h-16 text-sm"
                          placeholder="Image caption (optional)"></textarea>
            </div>
        </div>
    </template>
</div>
