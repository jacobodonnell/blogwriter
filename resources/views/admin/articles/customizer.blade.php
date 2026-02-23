<x-layouts.customizer :title="($isNew ?? false) ? 'New Article' : 'Edit: ' . $article->title" :article="$article">

    <x-slot:preview>
        @include('admin.articles.preview')
    </x-slot:preview>

    <div @photo-attached="handlePhotoAttached($event.detail)"
         x-data="articleCustomizer({
            title: @js(old('title', $article->title ?? '')),
            slug: @js(old('slug', $article->slug ?? '')),
            content: @js(old('content', $article->content ?? '')),
            summary: @js(old('summary', $article->summary ?? '')),
            selectedPhotoId: @js(old('photo_id', $article->photo_id ?? '')),
            featuredImageUrl: @js(old('featured_image', $article->meta['featured_image_url'] ?? '')),
            showUrlField: @js(!empty(old('featured_image', $article->meta['featured_image_url'] ?? ''))),
            featuredImageCaption: @js(old('meta.featured_image_caption', $article->meta['featured_image_caption'] ?? '')),
            usePhotoCaption: @js(old('meta.use_photo_caption', !empty($article->meta['use_photo_caption']))),
            initialStatus: @js($article->status->value),
            currentStatus: @js($article->status->value),
            wasEverPublished: @js($article->published_at !== null),
            originalPublishedAt: @js($article->published_at?->format('F j, Y')),
            saveRoute: @js(($isNew ?? false) ? route('admin.articles.store') : route('admin.articles.update', $article)),
            isNew: @js($isNew ?? false),
            hasDraft: @js($article->exists && $article->hasDraft()),
         })">

        <form x-ref="customizerForm"
              method="POST"
              enctype="multipart/form-data"
              action="{{ ($isNew ?? false) ? route('admin.articles.preview.store') : route('admin.articles.preview.update', $article) }}"
              x-target="preview-panel"
              @input.debounce.300ms="$el.requestSubmit()"
              novalidate>
            @csrf
            @unless($isNew ?? false)
                @method('PUT')
            @endunless

            {{-- Hidden file inputs for staged photo upload --}}
            <input type="file" x-ref="featuredImageFileInput" name="featured_image_file" class="hidden">
            <input type="hidden" x-ref="featuredImageAltInput" name="featured_image_alt" value="">
            <input type="hidden" x-ref="featuredImageCaptionInput" name="featured_image_caption" value="">

            {{-- Hidden inputs for staged new category --}}
            <input type="hidden" name="new_category_name" x-ref="newCategoryNameInput">
            <input type="hidden" name="new_category_slug" x-ref="newCategorySlugInput">
            <input type="hidden" name="new_category_parent_id" x-ref="newCategoryParentIdInput">
            <input type="hidden" name="new_category_description" x-ref="newCategoryDescriptionInput">
            <input type="hidden" name="meta[featured_image_caption]" :value="usePhotoCaption ? '' : featuredImageCaption">
            <input type="hidden" name="meta[use_photo_caption]" :value="usePhotoCaption ? '1' : ''">

            <div :class="fullWidth && 'max-w-5xl mx-auto w-full'">
            <div :class="fullWidth ? 'grid grid-cols-[1fr_320px] gap-6 items-start' : 'space-y-4'">

                {{-- Main column: title, slug, content --}}
                <div class="space-y-4 min-w-0 max-w-3xl">

                    {{-- Title --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Title</legend>
                        <input type="text" name="title" x-model="title"
                               @blur="generateSlug()"
                               class="input input-bordered w-full @error('title') input-error @enderror"
                               placeholder="Article title">
                        @error('title')
                        <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    {{-- Slug --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Slug</legend>
                        <input type="hidden" name="slug" :value="slug">
                        <div class="join w-full">
                            <span class="join-item btn btn-sm btn-disabled no-animation">/articles/</span>
                            <input type="text" id="display-slug" x-model="displaySlug"
                                   class="join-item input input-bordered input-sm flex-1 @error('slug') input-error @enderror"
                                   placeholder="auto-generated from title">
                        </div>
                        @error('slug')
                        <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    {{-- Content --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Content</legend>

                        {{-- Hidden field for form submission --}}
                        <input type="hidden" name="content" :value="content">

                        {{-- Skeleton placeholder while Tiptap initializes --}}
                        <div x-show="!editorReady" x-transition:leave x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-2">
                            <div class="skeleton h-10 w-full rounded"></div>
                            <div class="skeleton h-64 w-full rounded"></div>
                        </div>

                        <div :class="!editorReady && 'h-0 overflow-hidden'">
                            {{-- Tiptap toolbar --}}
                            <div class="tiptap-toolbar flex flex-wrap items-center gap-1 p-2 bg-base-200 border border-base-content/20 border-b-0 rounded-t-field">
                                <button type="button" @click="command('bold')" :class="isActive('bold') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Bold" data-test="toolbar-bold">
                                    <i class="ph ph-text-b"></i>
                                </button>
                                <button type="button" @click="command('italic')" :class="isActive('italic') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Italic" data-test="toolbar-italic">
                                    <i class="ph ph-text-italic"></i>
                                </button>
                                <div class="divider divider-horizontal mx-0"></div>
                                <button type="button" @click="command('h2')" :class="isActive('heading', {level:2}) && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 2" data-test="toolbar-h2">H2</button>
                                <button type="button" @click="command('h3')" :class="isActive('heading', {level:3}) && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 3">H3</button>
                                <button type="button" @click="command('h4')" :class="isActive('heading', {level:4}) && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 4">H4</button>
                                <button type="button" @click="command('h5')" :class="isActive('heading', {level:5}) && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Heading 5">H5</button>
                                <div class="divider divider-horizontal mx-0"></div>
                                <button type="button" @click="command('blockquote')" :class="isActive('blockquote') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Blockquote" data-test="toolbar-blockquote">
                                    <i class="ph ph-quotes"></i>
                                </button>
                                <button type="button" @click="command('bulletList')" :class="isActive('bulletList') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Bullet List" data-test="toolbar-bullet-list">
                                    <i class="ph ph-list-bullets"></i>
                                </button>
                                <button type="button" @click="command('orderedList')" :class="isActive('orderedList') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Ordered List" data-test="toolbar-ordered-list">
                                    <i class="ph ph-list-numbers"></i>
                                </button>
                                <div class="divider divider-horizontal mx-0"></div>
                                <button type="button" @click="command('link')"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Link">
                                    <i class="ph ph-link"></i>
                                </button>
                                <button type="button" @click="command('image')"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Image">
                                    <i class="ph ph-image"></i>
                                </button>
                                <button type="button" @click="command('code')" :class="isActive('code') && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Inline Code">
                                    <i class="ph ph-code"></i>
                                </button>
                                <button type="button" @click="command('horizontalRule')"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Horizontal Rule">
                                    <i class="ph ph-minus"></i>
                                </button>
                                <button type="button" @click="command('youtube')"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="YouTube" data-test="toolbar-youtube">
                                    <i class="ph ph-youtube-logo"></i>
                                </button>
                            </div>

                            {{-- Contextual image toolbar (shows when image is selected) --}}
                            <div x-show="isActive('image')" x-cloak
                                 class="flex items-center gap-1 px-2 py-1 bg-base-200 border border-base-content/20 border-t-0 border-b-0">
                                <span class="text-xs text-base-content/50 mr-1">Image:</span>
                                <button type="button" @click="command('imageFullWidth')"
                                        :class="isImageFullWidth() && 'btn-active'"
                                        class="btn btn-ghost btn-xs btn-square tooltip" data-tip="Full Width">
                                    <i class="ph ph-arrows-out-line-horizontal"></i>
                                </button>
                                <div class="divider divider-horizontal mx-0"></div>
                                <button type="button" @click="openEditImage()"
                                        class="btn btn-ghost btn-xs tooltip" data-tip="Edit image">
                                    <i class="ph ph-pencil-simple"></i> Edit
                                </button>
                            </div>

                            {{-- Tiptap editor mount point --}}
                            <div x-ref="contentEditor" data-test="content-editor"
                                 class="tiptap-editor @error('content') ring-2 ring-error @enderror border border-base-content/20 bg-base-100 min-h-64 h-96 max-h-[80vh] overflow-y-auto resize-y focus-within:outline-2 focus-within:outline-primary/20"></div>

                            {{-- Word count status bar --}}
                            <div class="flex justify-end px-2 py-1 border border-base-content/20 border-t-0 bg-base-200 rounded-b-field text-xs text-base-content/50"
                                 x-show="editorReady" x-cloak>
                                <span x-text="wordCount + ' words'"></span>
                            </div>

                            {{-- Inline dialogs for link / image / youtube --}}
                            <div x-show="showLinkDialog" class="flex gap-2 mt-2 items-center">
                                <input x-model="linkUrl" type="url" id="link-url" placeholder="https://..." class="input input-sm input-bordered flex-1" data-test="link-url-input" @keydown.enter.prevent="insertLink()">
                                <button type="button" @click="insertLink()" class="btn btn-sm btn-primary" data-test="link-insert-btn">Insert</button>
                                <button type="button" @click="showLinkDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
                            </div>
                            <div x-show="showImageDialog" class="mt-2 p-3 border border-base-content/20 rounded-field bg-base-50 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium" x-text="editingImage ? 'Edit Image' : 'Insert Image'"></span>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <input x-model="imageUrl" type="url" id="image-url" placeholder="Image URL (https://...)" class="input input-sm input-bordered flex-1" data-test="image-url-input" @keydown.enter.prevent="insertImage()">
                                </div>
                                <div class="flex gap-2">
                                    <input x-model="imageAlt" type="text" id="image-alt" placeholder="Alt text" class="input input-sm input-bordered flex-1" data-test="image-alt-input">
                                    <input x-model="imageCaption" type="text" id="image-caption" placeholder="Caption (optional)" class="input input-sm input-bordered flex-1" data-test="image-caption-input">
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1"></div>
                                    <button type="button" @click="insertImage()" class="btn btn-sm btn-primary" data-test="image-insert-btn">
                                        <span x-text="editingImage ? 'Update' : 'Insert'"></span>
                                    </button>
                                    <button type="button" @click="showImageDialog = false; editingImage = false" class="btn btn-sm btn-ghost">Cancel</button>
                                </div>
                            </div>
                            <div x-show="showYoutubeDialog" class="flex gap-2 mt-2 items-center">
                                <input x-model="youtubeUrl" type="url" id="youtube-url" placeholder="YouTube URL..." class="input input-sm input-bordered flex-1" data-test="youtube-url-input" @keydown.enter.prevent="insertYoutube()">
                                <button type="button" @click="insertYoutube()" class="btn btn-sm btn-primary" data-test="youtube-embed-btn">Embed</button>
                                <button type="button" @click="showYoutubeDialog = false" class="btn btn-sm btn-ghost">Cancel</button>
                            </div>
                        </div>

                        @error('content')
                        <div role="alert" class="alert alert-error mt-2" x-data="{ show: true }" x-show="show"
                             x-init="setTimeout(() => show = false, 8000)" x-transition>
                            <i class="ph ph-x-circle text-xl"></i>
                            <span>{{ $message }}</span>
                        </div>
                        @enderror

                        {{-- Client-side: content required warning --}}
                        <div x-show="contentError" x-cloak x-transition role="alert" class="alert alert-error mt-2">
                            <i class="ph ph-x-circle text-xl"></i>
                            <span>Please add some content before saving.</span>
                        </div>

                        {{-- H1 Warning (client-side only, hidden when server already shows error) --}}
                        @unless($errors->has('content'))
                            <div x-show="/^# (?!#)/m.test(content)" x-cloak x-transition class="alert alert-warning mt-2">
                                <i class="ph ph-warning text-xl"></i>
                                <span>H1 headings (#) are not allowed — the article title is already H1. Use ## or smaller.</span>
                            </div>
                        @endunless
                    </fieldset>

                    {{-- Summary --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Summary</legend>
                        <textarea name="summary" x-model="summary"
                                  data-test="summary-field"
                                  class="textarea textarea-bordered w-full h-20 text-sm @error('summary') textarea-error @enderror"
                                  placeholder="Auto-generated if empty">{{ old('summary', $article->summary) }}</textarea>
                        @error('summary')
                        <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>

                </div>

                {{-- Sidebar column: secondary fields + save --}}
                <div class="space-y-4" :class="fullWidth && 'pt-6'">

                    {{-- Save button (full-width mode only) --}}
                    <template x-if="fullWidth">
                        <div>
                            <x-article-save-button :article="$article"/>
                        </div>
                    </template>

                    {{-- Status --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Status</legend>
                        <select name="status" x-model="currentStatus" data-test="status-select"
                                class="select select-bordered w-full @error('status') select-error @enderror">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                        @error('status')
                        <span class="text-error text-sm">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    {{-- Category --}}
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Category</legend>
                        <x-category-select :categories="$categories ?? collect()"
                            :selected="$article->category_id"
                            x-show="!hasNewCategory"
                            @category-attached.window="
                                $refs.newCategoryNameInput.value = $event.detail.name;
                                $refs.newCategorySlugInput.value = $event.detail.slug ?? '';
                                $refs.newCategoryParentIdInput.value = $event.detail.parentId ?? '';
                                $refs.newCategoryDescriptionInput.value = $event.detail.description ?? '';
                                newCategoryName = $event.detail.name;
                                hasNewCategory = true;
                            " />
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
                        <legend class="fieldset-legend">Featured Image</legend>
                        @include('admin.articles.partials.featured-image-compact')
                    </fieldset>

                    {{-- SEO Settings --}}
                    <details class="collapse collapse-arrow bg-base-200 rounded-lg">
                        <summary class="collapse-title text-sm font-medium">
                            <i class="ph ph-magnifying-glass mr-1"></i> SEO Settings
                        </summary>
                        <div class="collapse-content space-y-3">
                            @php $meta = old('meta', $article->meta ?? []); @endphp

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Meta Title</legend>
                                <input type="text" name="meta[meta_title]"
                                       class="input input-bordered input-sm w-full"
                                       value="{{ $meta['meta_title'] ?? '' }}"
                                       placeholder="Custom search title">
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Meta Description</legend>
                                <textarea name="meta[meta_description]"
                                          class="textarea textarea-bordered w-full h-16 text-sm"
                                          placeholder="Search result description">{{ $meta['meta_description'] ?? '' }}</textarea>
                            </fieldset>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">OG Image URL</legend>
                                <input type="url" name="meta[og_image]"
                                       class="input input-bordered input-sm w-full"
                                       value="{{ $meta['og_image'] ?? '' }}"
                                       placeholder="https://example.com/og-image.jpg">
                            </fieldset>
                        </div>
                    </details>


                </div>

            </div>
            </div>

            {{-- Sticky bottom save button (normal mode only) --}}
            <template x-if="!fullWidth">
                <div class="sticky bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-base-100 from-60% to-transparent pt-8">
                    <x-article-save-button :article="$article"/>
                </div>
            </template>
        </form>

        {{-- Publish Modal --}}
        <x-editor-modal x-ref="publishModal" title="Publish this article?">
            <p>This article will be live and visible to everyone.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success"
                        @click="$refs.publishModal.close(); submitFullSave()">
                    Publish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Republish Modal --}}
        <x-editor-modal x-ref="republishModal" title="Republish this article?">
            <p>This article was originally published on <strong x-text="originalPublishedAt"></strong>. The original
                publish date will be preserved.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success"
                        @click="$refs.republishModal.close(); submitFullSave()">
                    Republish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Unpublish Modal --}}
        <x-editor-modal x-ref="unpublishModal" title="Revert to draft?">
            <p>This article will no longer be visible on your site. Anyone with the link will see a 404 until you
                republish.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-error"
                        @click="$refs.unpublishModal.close(); submitFullSave()">
                    Unpublish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- New Category Modal — stages data client-side, submits with main form --}}
        <x-editor-modal x-ref="newCategoryModal" title="New Category">
            <div x-data="{
                name: '',
                slug: '',
                description: '',
                parentId: '',
                slugManuallyEdited: false,
                autoSlug() {
                    if (!this.slugManuallyEdited) {
                        this.slug = this.name.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                },
                reset() {
                    this.name = ''; this.slug = ''; this.description = '';
                    this.parentId = ''; this.slugManuallyEdited = false;
                },
            }">
                <div class="space-y-3">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Name</legend>
                        <input type="text" id="new-category-name" x-model="name" @blur="autoSlug()"
                               class="input input-bordered w-full"
                               placeholder="e.g. Web Development">
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Slug (optional)</legend>
                        <input type="text" id="new-category-slug" x-model="slug" @input="slugManuallyEdited = true"
                               class="input input-bordered w-full"
                               placeholder="auto-generated from name">
                        <p class="fieldset-label">Leave blank to auto-generate from name.</p>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Parent Category (optional)</legend>
                        <x-category-select :categories="$categories ?? collect()"
                            :selected="null"
                            name="_modal_parent_id"
                            emptyLabel="None (top-level)"
                            x-model="parentId" />
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Description (optional)</legend>
                        <textarea id="new-category-description" x-model="description"
                                  class="textarea textarea-bordered w-full h-16 text-sm"
                                  placeholder="Brief description of this category"></textarea>
                    </fieldset>

                    <div class="alert alert-info text-sm">
                        <i class="ph ph-info"></i>
                        <span>This category will be created when you save the article.</span>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-primary"
                            @click="
                                if (!name.trim()) return;
                                $dispatch('category-attached', {
                                    name: name.trim(),
                                    slug: slug.trim() || null,
                                    parentId: parentId || null,
                                    description: description.trim() || null,
                                });
                                $el.closest('dialog').close();
                                reset();
                            ">
                        Attach Category
                    </button>
                    <form method="dialog">
                        <button class="btn" @click="reset()">Cancel</button>
                    </form>
                </div>
            </div>
        </x-editor-modal>

        {{-- Upload Photo Modal — stages file client-side, submits with main form --}}
        <x-editor-modal x-ref="uploadPhotoModal" title="Upload Featured Image" maxWidth="max-w-xl">
            <div x-data="uploadPhotoModal()">
                <div class="space-y-3">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Image</legend>
                        <input type="file" id="photo-file-picker" x-ref="filePicker" data-test="photo-file-picker"
                               class="file-input file-input-bordered w-full"
                               accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                               @change="handleFileChange($event)">
                        <img x-show="uploadPreview" :src="uploadPreview"
                             class="w-full max-h-40 object-contain rounded-lg mt-2"
                             alt="Upload preview"
                             x-cloak>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Alt Text</legend>
                        <input type="text" id="photo-alt-text" x-ref="altInput" data-test="photo-alt-text"
                               class="input input-bordered w-full"
                               placeholder="Describe the image for accessibility">
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Caption (optional)</legend>
                        <textarea id="photo-caption" x-ref="captionInput"
                                  class="textarea textarea-bordered w-full h-16 text-sm"
                                  placeholder="Photo caption"></textarea>
                    </fieldset>

                    <div class="alert alert-warning text-sm mt-3">
                        <i class="ph ph-warning"></i>
                        <span>This photo will be published when you save the article.</span>
                    </div>
                </div>

                {{-- Action buttons rendered inline (inside uploadPhotoModal scope for $refs access) --}}
                <div class="modal-action">
                    <button type="button" class="btn btn-primary" data-test="attach-photo"
                            @click="
                                if (!$refs.filePicker.files[0] || !$refs.altInput.value.trim()) return;
                                $dispatch('photo-attached', {
                                    file: $refs.filePicker.files[0],
                                    alt: $refs.altInput.value,
                                    caption: $refs.captionInput.value
                                });
                                $el.closest('dialog').close();
                            ">
                        Attach Photo
                    </button>
                    <form method="dialog">
                        <button class="btn">Cancel</button>
                    </form>
                </div>
            </div>
        </x-editor-modal>

    </div>

</x-layouts.customizer>
