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
            featuredImageCaption: @js(old('meta.featured_image_caption', $article->meta['featured_image_caption'] ?? '')),
            usePhotoCaption: @js(old('meta.use_photo_caption', !empty($article->meta['use_photo_caption']))),
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

            hasNewPhoto: false,

            get buttonAction() {
                if (this.currentStatus === 'published' && this.initialStatus === 'draft' && !this.wasEverPublished) return 'publish';
                if (this.currentStatus === 'published' && this.initialStatus === 'draft' && this.wasEverPublished) return 'republish';
                if (this.currentStatus === 'draft' && this.initialStatus === 'published') return 'unpublish';
                return 'save';
            },
            get buttonLabel() {
                const a = this.buttonAction;
                const p = this.hasNewPhoto;
                if (a === 'publish') return p ? 'Upload Photo & Publish' : 'Publish Article';
                if (a === 'republish') return p ? 'Upload Photo & Republish' : 'Republish Article';
                if (a === 'unpublish') return 'Unpublish Article';
                if (this.initialStatus === 'published') return p ? 'Upload Photo & Save Changes' : 'Save Changes';
                return p ? 'Upload Photo & Save Draft' : 'Save Draft';
            },
            get buttonIcon() {
                if (this.hasNewPhoto) return 'ph-upload-simple';
                if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'ph-rocket-launch';
                if (this.buttonAction === 'unpublish') return 'ph-arrow-u-up-left';
                return 'ph-floppy-disk';
            },
            get buttonClass() {
                if (this.hasNewPhoto) return 'btn-success';
                if (this.buttonAction === 'publish' || this.buttonAction === 'republish') return 'btn-success';
                if (this.buttonAction === 'unpublish') return 'btn-error btn-outline';
                return 'btn-primary';
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
              @input.debounce.300ms="$el.requestSubmit()"
              novalidate>
            @csrf
            @unless($isNew ?? false)
                @method('PUT')
            @endunless

            {{-- Hidden file inputs for staged photo upload --}}
            <input type="file" id="featured-image-file-input" name="featured_image_file" class="hidden">
            <input type="hidden" id="featured-image-alt-input" name="featured_image_alt" value="">
            <input type="hidden" id="featured-image-caption-input" name="featured_image_caption" value="">
            <input type="hidden" name="meta[featured_image_caption]" :value="usePhotoCaption ? '' : featuredImageCaption">
            <input type="hidden" name="meta[use_photo_caption]" :value="usePhotoCaption ? '1' : ''">

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
                        <span class="join-item btn btn-sm btn-disabled no-animation">/articles/</span>
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
                              class="textarea textarea-bordered w-full h-64 font-mono text-sm @error('content') textarea-error @enderror"
                              placeholder="## Write your article here...">{{ old('content', $article->content) }}</textarea>
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
                              class="textarea textarea-bordered w-full h-20 text-sm @error('summary') textarea-error @enderror"
                              placeholder="Auto-generated if empty">{{ old('summary', $article->summary) }}</textarea>
                    @error('summary')
                    <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </fieldset>

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
                    <select name="category_id" class="select select-bordered w-full">
                        <option value="">No Category</option>
                        @forelse($categories ?? [] as $rootCat)
                            <option value="{{ $rootCat->id }}" {{ old('category_id', $article->category_id) == $rootCat->id ? 'selected' : '' }}>
                                {{ $rootCat->name }}
                            </option>
                            @foreach($rootCat->children as $childCat)
                                <option value="{{ $childCat->id }}" {{ old('category_id', $article->category_id) == $childCat->id ? 'selected' : '' }}>
                                    &nbsp;&nbsp;└ {{ $childCat->name }}
                                </option>
                            @endforeach
                        @empty
                            <option disabled>No categories. Create one first.</option>
                        @endforelse
                    </select>
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
                <x-article-save-button :article="$article"/>
            </div>
        </form>

        {{-- Publish Modal --}}
        <x-editor-modal id="publish-modal" title="Publish this article?">
            <p>This article will be live and visible to everyone.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success"
                        @click="document.getElementById('publish-modal').close(); submitFullSave()">
                    Publish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Republish Modal --}}
        <x-editor-modal id="republish-modal" title="Republish this article?">
            <p>This article was originally published on <strong x-text="originalPublishedAt"></strong>. The original
                publish date will be preserved.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-success"
                        @click="document.getElementById('republish-modal').close(); submitFullSave()">
                    Republish
                </button>
            </x-slot:actions>
        </x-editor-modal>

        {{-- Unpublish Modal --}}
        <x-editor-modal id="unpublish-modal" title="Revert to draft?">
            <p>This article will no longer be visible on your site. Anyone with the link will see a 404 until you
                republish.</p>

            <x-slot:actions>
                <button type="button" class="btn btn-error"
                        @click="document.getElementById('unpublish-modal').close(); submitFullSave()">
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
                        <input type="file" id="photo-file-picker" data-test="photo-file-picker"
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
                        <input type="text" id="photo-alt-text-input" data-test="photo-alt-text"
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
                <button type="button" class="btn btn-primary" data-test="attach-photo"
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
                            hasNewPhoto = true;

                            document.getElementById('upload-photo-modal').close();
                        ">
                    Attach Photo
                </button>
            </x-slot:actions>
        </x-editor-modal>

    </div>

</x-layouts.customizer>
