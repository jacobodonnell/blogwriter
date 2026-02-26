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

    {{-- Slug --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Slug (optional)</legend>
        <input type="text"
               name="slug"
               class="input input-bordered w-full font-mono @error('slug') input-error @enderror"
               value="{{ old('slug', $photo->slug) }}"
               placeholder="Leave blank to derive from filename">
        <p class="text-xs text-base-content/50 mt-1">
            Used in the public URL: /photos/your-slug. Lowercase letters, numbers, and hyphens only (e.g. my-photo).
        </p>
        @error('slug')
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

    {{-- Category --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Category</legend>
        <x-category-select :categories="$categories ?? collect()" :selected="$photo->category_id" />
        @error('category_id')
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
                <x-photo-exif-details :photo="$photo" />
            </div>
        </details>
    @endif
</div>
