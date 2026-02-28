@props(['article', 'categories', 'photos', 'isNew'])

<div class="space-y-4" :class="classicEditor && 'md:pt-6'">

    {{-- Save button (classic editor mode only) --}}
    <template x-if="classicEditor">
        <div>
            <x-article-save-button :article="$article"/>
        </div>
    </template>

    {{-- Status --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">Status</legend>
        <select name="status" x-model="currentStatus" data-test="status-select"
                class="select select-bordered w-full @error('status') select-error @enderror">
            <option value="private">Private</option>
            <option value="public">Public</option>
        </select>
        @error('status')
        <span class="text-error text-sm">{{ $message }}</span>
        @enderror
    </fieldset>

    {{-- Category --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">
            Category
            <span :class="hasNewCategory ? 'invisible' : ''"><x-draft-revert-button field="categoryId"/></span>
        </legend>
        <x-category-select :categories="$categories ?? collect()"
                           :selected="$article->category_id"
                           x-show="!hasNewCategory"
                           x-model="categoryId"
                           @change="checkDirty(); $refs.customizerForm.dispatchEvent(new Event('input', { bubbles: true }))"
                           @category-attached.window="
                $refs.newCategoryNameInput.value = $event.detail.name;
                $refs.newCategorySlugInput.value = $event.detail.slug ?? '';
                $refs.newCategoryParentIdInput.value = $event.detail.parentId ?? '';
                $refs.newCategoryDescriptionInput.value = $event.detail.description ?? '';
                newCategoryName = $event.detail.name;
                hasNewCategory = true;
            "/>
        {{-- Staged new category badge --}}
        <div x-show="hasNewCategory" x-cloak
             class="flex items-center justify-between gap-2 mt-2 px-2 py-1.5 bg-success/10 border border-success/30 rounded-field text-sm">
            <span class="flex items-center gap-1.5 text-success font-medium">
                <i class="ph ph-tag"></i>
                <span x-text="newCategoryName"></span>
            </span>
            <button type="button"
                    class="btn btn-ghost btn-xs text-base-content/50 hover:text-error"
                    aria-label="Remove staged category"
                    @click="
                        hasNewCategory = false;
                        newCategoryName = '';
                        $refs.newCategoryNameInput.value = '';
                        $refs.newCategorySlugInput.value = '';
                        $refs.newCategoryParentIdInput.value = '';
                        $refs.newCategoryDescriptionInput.value = '';
                    ">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <button type="button"
                x-show="!hasNewCategory"
                class="btn btn-ghost btn-sm mt-2 gap-1 w-full justify-start"
                @click="$refs.newCategoryModal.showModal()">
            <i class="ph ph-plus"></i>
            New Category
        </button>
    </fieldset>

    {{-- Featured Image --}}
    <fieldset class="fieldset">
        <legend class="fieldset-legend">
            Featured Image
            <x-draft-revert-button field="featuredImage" method="revertFeaturedImage()"/>
        </legend>
        @include('admin.articles.partials.featured-image-compact')
    </fieldset>

    {{-- SEO Settings --}}
    <details class="collapse collapse-arrow bg-base-200 rounded-lg">
        <summary class="collapse-title text-sm font-medium" data-test="seo-toggle">
            <i class="ph ph-magnifying-glass mr-1"></i> SEO Settings
        </summary>
        <div class="collapse-content space-y-3">
            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs">
                    Meta Title
                    <x-draft-revert-button field="metaTitle"/>
                </legend>
                <input type="text" name="meta[meta_title]" x-model="metaTitle"
                       @input="checkDirty()"
                       class="input input-bordered input-sm w-full"
                       placeholder="Custom search title">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs">
                    Meta Description
                    <x-draft-revert-button field="metaDescription"/>
                </legend>
                <textarea name="meta[meta_description]" x-model="metaDescription"
                          @input="checkDirty()"
                          class="textarea textarea-bordered w-full h-16 text-sm"
                          placeholder="Search result description"></textarea>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend text-xs">
                    OG Image URL
                    <x-draft-revert-button field="ogImage"/>
                </legend>
                <input type="url" name="meta[og_image]" x-model="ogImage"
                       @input="checkDirty()"
                       class="input input-bordered input-sm w-full"
                       placeholder="https://example.com/og-image.jpg">
            </fieldset>
        </div>
    </details>

    @unless($isNew)
        {{-- Download --}}
        <a href="{{ route('admin.articles.download', $article) }}"
           class="btn btn-ghost btn-sm gap-2 w-full justify-start"
           data-test="article-download-btn">
            <i class="ph ph-download-simple text-lg"></i>
            Download Markdown
        </a>
    @endunless

</div>
