@php
    $photo = $photo ?? new \App\Models\Photo();
@endphp

<div class="space-y-6">
    {{-- Current Image Preview --}}
    @if($photo->exists && $photo->getFirstMedia('image'))
        <div class="mb-6">
            <label class="label">
                <span class="label-text font-semibold">Current Image</span>
            </label>
            <figure class="relative max-w-lg">
                <img src="{{ $photo->image_url }}"
                     alt="{{ $photo->alt_text }}"
                     class="w-full rounded-lg shadow-md">
            </figure>
        </div>
    @endif

    {{-- Image File Upload --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">
            {{ $photo->exists ? 'Replace Image (optional)' : 'Photo File' }}
        </legend>
        <input type="file"
               name="image_file"
               class="file-input file-input-bordered w-full @error('image_file') file-input-error @enderror"
               accept="image/*"
               {{ $photo->exists ? '' : 'required' }}>
        <p class="text-xs text-base-content/50 mt-1">
            Maximum file size: 10MB. Supported formats: JPG, PNG, WebP, GIF
        </p>
        @error('image_file')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Alt Text --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Alt Text (required)</legend>
        <input type="text"
               name="alt_text"
               class="input input-bordered w-full @error('alt_text') input-error @enderror"
               value="{{ old('alt_text', $photo->alt_text) }}"
               placeholder="Descriptive text for screen readers and SEO"
               maxlength="500"
               required>
        <p class="text-xs text-base-content/50 mt-1">
            Describe what's in the photo for accessibility (max 500 characters)
        </p>
        @error('alt_text')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Caption --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Caption (optional)</legend>
        <textarea name="caption"
                  rows="4"
                  class="textarea textarea-bordered w-full @error('caption') textarea-error @enderror"
                  placeholder="Add a caption for this photo...">{{ old('caption', $photo->caption) }}</textarea>
        <p class="text-xs text-base-content/50 mt-1">
            Supports Markdown formatting
        </p>
        @error('caption')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Status --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <div role="tablist" class="tabs tabs-box">
            <input type="radio"
                   name="status"
                   value="draft"
                   class="tab"
                   aria-label="📝 Draft"
                   @change="currentStatus = 'draft'"
                   {{ old('status', $photo->status?->value ?? 'draft') === 'draft' ? 'checked' : '' }}>
            <input type="radio"
                   name="status"
                   value="published"
                   class="tab"
                   aria-label="✅ Published"
                   @change="currentStatus = 'published'"
                   {{ old('status', $photo->status?->value) === 'published' ? 'checked' : '' }}>
        </div>
        @error('status')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
        <p class="text-sm text-base-content/60 mt-2">
            Draft photos are stored privately and not visible on your site.
        </p>
    </fieldset>

    {{-- Taken At Date --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Taken At (optional)</legend>
        <input type="date"
               name="taken_at"
               class="input input-bordered w-full @error('taken_at') input-error @enderror"
               value="{{ old('taken_at', $photo->taken_at?->format('Y-m-d')) }}">
        <p class="text-xs text-base-content/50 mt-1">
            When was this photo taken?
        </p>
        @error('taken_at')
            <span class="text-error text-sm mt-1">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- EXIF Metadata (read-only, if editing) --}}
    @if($photo->exists && !empty($photo->meta))
        <div class="divider">Photo Details</div>

        <details class="collapse collapse-arrow bg-base-200 rounded-lg">
            <summary class="collapse-title font-medium">
                <i class="ph ph-info mr-2"></i>
                EXIF Metadata
            </summary>
            <div class="collapse-content">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-2">
                    @if($photo->meta['camera_model'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Camera:</dt>
                            <dd class="text-base-content">{{ $photo->meta['camera_model'] }}</dd>
                        </div>
                    @endif

                    @if($photo->meta['lens'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Lens:</dt>
                            <dd class="text-base-content">{{ $photo->meta['lens'] }}</dd>
                        </div>
                    @endif

                    @if($photo->meta['iso'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">ISO:</dt>
                            <dd class="text-base-content">{{ $photo->meta['iso'] }}</dd>
                        </div>
                    @endif

                    @if($photo->meta['aperture'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Aperture:</dt>
                            <dd class="text-base-content">f/{{ $photo->meta['aperture'] }}</dd>
                        </div>
                    @endif

                    @if($photo->meta['shutter_speed'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Shutter Speed:</dt>
                            <dd class="text-base-content">{{ $photo->meta['shutter_speed'] }}s</dd>
                        </div>
                    @endif

                    @if($photo->meta['focal_length'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Focal Length:</dt>
                            <dd class="text-base-content">{{ $photo->meta['focal_length'] }}mm</dd>
                        </div>
                    @endif

                    @if(($photo->meta['width'] ?? null) && ($photo->meta['height'] ?? null))
                        <div>
                            <dt class="font-semibold text-base-content/70">Dimensions:</dt>
                            <dd class="text-base-content">{{ $photo->meta['width'] }} × {{ $photo->meta['height'] }} px</dd>
                        </div>
                    @endif

                    @if($photo->meta['created_at'] ?? null)
                        <div>
                            <dt class="font-semibold text-base-content/70">Date Taken (EXIF):</dt>
                            <dd class="text-base-content">{{ $photo->meta['created_at'] }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </details>
    @endif
</div>
