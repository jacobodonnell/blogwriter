<x-layouts.customizer :title="($isNew ?? false) ? 'New Article' : 'Edit: ' . $article->title" :article="$article">

    <x-slot:preview>
        @include('admin.articles.preview')
    </x-slot:preview>

    <div x-data="{
            title: @js(old('title', $article->title ?? '')),
            slug: @js(old('slug', $article->slug ?? '')),
            content: @js(old('content', $article->content ?? '')),
            summary: @js(old('summary', $article->summary ?? '')),
            selectedPhotoId: @js(old('photo_id', $article->photo_id ?? '')),
            uploadedPhotoUrl: null,
            uploading: false,
            featuredImageUrl: @js(old('featured_image', $article->meta['featured_image_url'] ?? '')),
            showUrlField: @js(!empty(old('featured_image', $article->meta['featured_image_url'] ?? ''))),
            easyMDE: null,
            initialStatus: @js($article->status->value),
            currentStatus: @js($article->status->value),
            wasEverPublished: @js($article->published_at !== null),
            originalPublishedAt: @js($article->published_at?->format('F j, Y')),
            contentError: false,

            get isPlaceholderSlug() {
                return /^untitled-[a-z0-9]{8}$/.test(this.slug);
            },

            get displaySlug() {
                return this.isPlaceholderSlug ? '' : this.slug;
            },
            set displaySlug(v) {
                this.slug = v;
            },

            generateSlug() {
                if (this.title && (!this.slug || this.slug.match(/^untitled-[a-z0-9]{8}$/))) {
                    this.slug = this.title.toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                }
            },

            generateSummary() {
                if (!this.summary && this.content) {
                    this.summary = this.content.substring(0, 255);
                }
            },

            get hasNewPhoto() {
                const fileInput = document.getElementById('featured-image-file-input');
                return (fileInput && fileInput.files && fileInput.files.length > 0);
            },

            init() {
                this.$nextTick(() => {
                    const ta = document.getElementById('content-editor');
                    if (!ta) return;

                    this.easyMDE = new EasyMDE({
                        element: ta,
                        forceSync: true,
                        spellChecker: false,
                        status: false,
                        placeholder: '## Write your article here...',
                        toolbar: [
                            'bold', 'italic', 'heading-2', 'heading-3', '|',
                            'quote', 'unordered-list', 'ordered-list', '|',
                            'link', 'image', 'code', 'horizontal-rule', '|',
                            'guide'
                        ],
                        initialValue: this.content,
                    });

                    this.easyMDE.codemirror.on('change', () => {
                        this.content = this.easyMDE.value();
                        this.contentError = false;
                        document.getElementById('customizer-form').dispatchEvent(
                            new Event('input', { bubbles: true })
                        );
                    });
                });
            },

            submitFullSave() {
                if (this.easyMDE) {
                    this.content = this.easyMDE.value();
                }
                if (!this.content || !this.content.trim()) {
                    this.contentError = true;
                    if (this.easyMDE) {
                        this.easyMDE.codemirror.focus();
                    }
                    return;
                }
                let form = document.getElementById('customizer-form');
                form.removeAttribute('x-target');
                @if($isNew ?? false)
                    form.action = '{{ route("admin.articles.store") }}';
                @else
                    form.action = '{{ route("admin.articles.update", $article) }}';
                @endif
                form.submit();
            },

         }">

        <form id="customizer-form"
              method="POST"
              enctype="multipart/form-data"
              action="{{ ($isNew ?? false) ? route('admin.articles.preview.store') : route('admin.articles.preview.update', $article) }}"
              x-target="preview-panel"
              @ajax:success="saved = true; setTimeout(() => saved = false, 2000)"
              @input.debounce.600ms="$el.requestSubmit()"
              novalidate>
            @csrf
            @unless($isNew ?? false)
                @method('PUT')
            @endunless

            {{-- Hidden file inputs for staged photo upload --}}
            <input type="file" id="featured-image-file-input" name="featured_image_file" class="hidden">
            <input type="hidden" id="featured-image-alt-input" name="featured_image_alt" value="">
            <input type="hidden" id="featured-image-caption-input" name="featured_image_caption" value="">

            <div class="space-y-4">

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
                        <span class="join-item btn btn-sm btn-disabled no-animation">/blog/</span>
                        <input type="text" x-model="displaySlug"
                               class="join-item input input-bordered input-sm flex-1 @error('slug') input-error @enderror"
                               placeholder="auto-generated from title">
                    </div>
                    @error('slug')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- Content --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Content (Markdown)</legend>
                    <textarea id="content-editor" name="content" x-model="content"
                              @blur="generateSummary()"
                              class="textarea textarea-bordered w-full h-64 font-mono text-sm @error('content') textarea-error @enderror"
                              placeholder="## Write your article here...">{{ old('content', $article->content) }}</textarea>
                    @error('content')
                        <div role="alert" class="alert alert-error mt-2" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" x-transition>
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
                              class="textarea textarea-bordered w-full h-20 text-sm @error('summary') textarea-error @enderror"
                              placeholder="Auto-generated if empty">{{ old('summary', $article->summary) }}</textarea>
                    @error('summary')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- Status --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Status</legend>
                    <select name="status" x-model="currentStatus" class="select select-bordered w-full @error('status') select-error @enderror">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                    @error('status')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- Categories --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Categories</legend>
                    <div class="space-y-1 max-h-36 overflow-y-auto p-2 bg-base-200 rounded-lg">
                        @forelse($categories ?? [] as $category)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300 p-1 rounded transition-colors">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                       class="checkbox checkbox-sm"
                                       {{ in_array($category->id, old('categories', $article->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $category->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-base-content/50 italic p-2">No categories. <a href="{{ route('admin.categories.index') }}" class="link link-primary">Create one</a></p>
                        @endforelse
                    </div>
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

            {{-- Sticky bottom buttons --}}
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-base-100 from-60% to-transparent pt-8">
                <div class="flex flex-col gap-2">

                    {{-- Save Draft: draft → draft (never published) --}}
                    <button x-show="currentStatus === 'draft' && initialStatus === 'draft' && !wasEverPublished"
                            type="button"
                            @click="submitFullSave()"
                            class="btn btn-primary w-full gap-2">
                        <i class="ph" :class="hasNewPhoto ? 'ph-image' : 'ph-floppy-disk'"></i>
                        <span x-text="hasNewPhoto ? 'Publish Photo & Save Draft' : 'Save Draft'"></span>
                    </button>

                    {{-- Save Draft: draft → draft (was published before, staying draft) --}}
                    <button x-show="currentStatus === 'draft' && initialStatus === 'draft' && wasEverPublished"
                            type="button"
                            @click="submitFullSave()"
                            class="btn btn-primary w-full gap-2">
                        <i class="ph" :class="hasNewPhoto ? 'ph-image' : 'ph-floppy-disk'"></i>
                        <span x-text="hasNewPhoto ? 'Publish Photo & Save Draft' : 'Save Draft'"></span>
                    </button>

                    {{-- Publish: draft (never published) → published --}}
                    <button x-show="currentStatus === 'published' && initialStatus === 'draft' && !wasEverPublished"
                            type="button"
                            @click="document.getElementById('publish-modal').showModal()"
                            class="btn btn-success w-full gap-2">
                        <i class="ph" :class="hasNewPhoto ? 'ph-image' : 'ph-rocket-launch'"></i>
                        <span x-text="hasNewPhoto ? 'Publish Photo & Publish Article' : 'Publish Article'"></span>
                    </button>

                    {{-- Republish: draft (was published) → published --}}
                    <button x-show="currentStatus === 'published' && initialStatus === 'draft' && wasEverPublished"
                            type="button"
                            @click="document.getElementById('republish-modal').showModal()"
                            class="btn btn-success w-full gap-2">
                        <i class="ph" :class="hasNewPhoto ? 'ph-image' : 'ph-rocket-launch'"></i>
                        <span x-text="hasNewPhoto ? 'Publish Photo & Republish' : 'Republish Article'"></span>
                    </button>

                    {{-- Save Changes: published → published --}}
                    <button x-show="currentStatus === 'published' && initialStatus === 'published'"
                            type="button"
                            @click="submitFullSave()"
                            class="btn btn-primary w-full gap-2">
                        <i class="ph" :class="hasNewPhoto ? 'ph-image' : 'ph-floppy-disk'"></i>
                        <span x-text="hasNewPhoto ? 'Publish Photo & Save Changes' : 'Save Changes'"></span>
                    </button>

                    {{-- Unpublish: published → draft --}}
                    <button x-show="currentStatus === 'draft' && initialStatus === 'published'"
                            type="button"
                            @click="document.getElementById('unpublish-modal').showModal()"
                            class="btn btn-error btn-outline w-full gap-2">
                        <i class="ph ph-arrow-u-up-left"></i>
                        Unpublish Article
                    </button>

                    {{-- View Live button (only when currently published on server) --}}
                    @if($article->exists && $article->isPublished())
                        <a href="{{ route('article.show', $article->slug) }}"
                           x-show="initialStatus === 'published'"
                           class="btn btn-outline w-full gap-2">
                            <i class="ph ph-arrow-square-out"></i>
                            View Live
                        </a>
                    @endif

                    {{-- Status hint when switching to unpublish --}}
                    <p x-show="currentStatus === 'draft' && initialStatus === 'published'"
                       class="text-xs text-center text-base-content/50" x-cloak>
                        Currently live — unpublishing will return a 404 to visitors.
                    </p>

                    {{-- Status hint for new publish --}}
                    <p x-show="currentStatus === 'published' && initialStatus === 'draft' && !wasEverPublished"
                       class="text-xs text-center text-base-content/50" x-cloak>
                        This article has never been published.
                    </p>

                    {{-- Status hint for republish --}}
                    <p x-show="currentStatus === 'published' && initialStatus === 'draft' && wasEverPublished"
                       class="text-xs text-center text-base-content/50" x-cloak>
                        Previously published <span x-text="originalPublishedAt"></span> — original date will be preserved.
                    </p>
                </div>
            </div>
        </form>

        {{-- Publish Modal --}}
        <x-editor-modal id="publish-modal" title="Publish this article?">
            <p>This article will be live and visible to everyone.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success" @click="document.getElementById('publish-modal').close(); submitFullSave()">
                    Publish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Republish Modal --}}
        <x-editor-modal id="republish-modal" title="Republish this article?">
            <p>This article was originally published on <strong x-text="originalPublishedAt"></strong>. The original publish date will be preserved.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success" @click="document.getElementById('republish-modal').close(); submitFullSave()">
                    Republish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Unpublish Modal --}}
        <x-editor-modal id="unpublish-modal" title="Revert to draft?">
            <p>This article will no longer be visible on your site. Anyone with the link will see a 404 until you republish.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-error" @click="document.getElementById('unpublish-modal').close(); submitFullSave()">
                    Unpublish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Upload Photo Modal — stages file client-side, submits with main form --}}
        <x-editor-modal id="upload-photo-modal" title="Upload Featured Image" maxWidth="max-w-xl">
            <div x-data="{ uploadPreview: null }">
                <div class="space-y-3">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Image</legend>
                        <input type="file" id="photo-file-picker"
                               class="file-input file-input-bordered w-full"
                               accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
                               @change="if ($event.target.files[0]) { uploadPreview = URL.createObjectURL($event.target.files[0]); }">
                        <img x-show="uploadPreview" :src="uploadPreview"
                             class="w-full max-h-40 object-cover rounded-lg mt-2"
                             alt="Upload preview"
                             x-cloak>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Alt Text</legend>
                        <input type="text" id="photo-alt-text-input"
                               class="input input-bordered w-full"
                               placeholder="Describe the image for accessibility">
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Caption (optional)</legend>
                        <textarea id="photo-caption-input"
                                  class="textarea textarea-bordered w-full h-16 text-sm"
                                  placeholder="Photo caption"></textarea>
                    </fieldset>

                    <div class="alert alert-warning text-sm mt-3">
                        <i class="ph ph-warning"></i>
                        <span>This photo will be published when you save the article.</span>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <button type="button" class="btn btn-primary"
                        @click="
                            const picker = document.getElementById('photo-file-picker');
                            const altInput = document.getElementById('photo-alt-text-input');
                            if (!picker.files[0] || !altInput.value.trim()) return;

                            // Transfer file to hidden input on main form
                            const dt = new DataTransfer();
                            dt.items.add(picker.files[0]);
                            document.getElementById('featured-image-file-input').files = dt.files;
                            document.getElementById('featured-image-alt-input').value = altInput.value.trim();
                            document.getElementById('featured-image-caption-input').value = document.getElementById('photo-caption-input').value;

                            // Update Alpine state for preview + button text
                            uploadedPhotoUrl = URL.createObjectURL(picker.files[0]);
                            selectedPhotoId = '';
                            featuredImageUrl = '';
                            showUrlField = false;

                            document.getElementById('upload-photo-modal').close();
                        ">
                    Attach Photo
                </button>
            </x-slot:actions>
        </x-editor-modal>

    </div>

</x-layouts.customizer>
