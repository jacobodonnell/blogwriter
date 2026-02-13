@php
    $featuredPhoto = $article->exists ? $article->featuredPhoto : null;
    $initialPhotoId = old('photo_id', $article->photo_id ?? '');
@endphp

<div x-data="{
        imgTab: '{{ $initialPhotoId ? "photo" : "url" }}',
     }">

    {{-- Tab Navigation --}}
    <div class="tabs tabs-boxed tabs-sm mb-3">
        <button type="button" class="tab" :class="{ 'tab-active': imgTab === 'photo' }" @click="imgTab = 'photo'">Photo</button>
        <button type="button" class="tab" :class="{ 'tab-active': imgTab === 'url' }" @click="imgTab = 'url'">URL</button>
        <button type="button" class="tab" :class="{ 'tab-active': imgTab === 'upload' }" @click="imgTab = 'upload'">Upload</button>
    </div>

    {{-- Photo Tab --}}
    <div x-show="imgTab === 'photo'" x-transition>
        <select name="photo_id" class="select select-bordered select-sm w-full">
            <option value="">No featured image</option>
            @foreach(\App\Models\Photo::latest()->limit(50)->get() as $photo)
                <option value="{{ $photo->id }}" {{ $initialPhotoId == $photo->id ? 'selected' : '' }}>
                    {{ $photo->alt_text }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- URL Tab --}}
    <div x-show="imgTab === 'url'" x-transition>
        <input type="url" name="featured_image"
               class="input input-bordered input-sm w-full"
               placeholder="https://example.com/image.jpg"
               value="{{ old('featured_image', $article->meta['featured_image_url'] ?? '') }}">
    </div>

    {{-- Upload Tab --}}
    <div x-show="imgTab === 'upload'" x-transition>
        <input type="file" name="featured_image_file"
               class="file-input file-input-bordered file-input-sm w-full"
               accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
               @change="hasFileUpload = true">
        <p class="text-xs text-base-content/50 mt-1">Requires manual save</p>
    </div>

    {{-- Current Image Thumbnail --}}
    @if($article->featured_image_url)
        <div class="mt-3">
            <p class="text-xs font-medium mb-1">Current:</p>
            <img src="{{ $article->featured_image_url }}"
                 alt="{{ $featuredPhoto?->alt_text ?? 'Featured image' }}"
                 class="w-full max-h-32 object-cover rounded-lg">
        </div>
    @endif
</div>
