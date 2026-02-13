<x-layouts.customizer :title="'Edit: ' . $article->title" :article="$article">

    <x-slot:preview>
        @include('admin.articles.preview')
    </x-slot:preview>

    <div x-data="{
            title: @js(old('title', $article->title ?? '')),
            slug: @js(old('slug', $article->slug ?? '')),
            content: @js(old('content', $article->content ?? '')),
            summary: @js(old('summary', $article->summary ?? '')),
            hasFileUpload: false,
            uploadNotice: false,

            generateSlug() {
                if (!this.slug && this.title) {
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

         }">

        {{-- Upload Notice Toast --}}
        <div x-show="uploadNotice"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="alert alert-info alert-sm mb-3 text-sm py-2"
             x-cloak>
            <i class="ph ph-info text-lg"></i>
            <span>Image selected — press the button below to upload and save.</span>
            <button type="button" @click="uploadNotice = false" class="btn btn-ghost btn-xs btn-circle">
                <i class="ph ph-x"></i>
            </button>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-error mb-4">
                <i class="ph ph-x-circle text-xl"></i>
                <div class="flex flex-col">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.articles.update', $article) }}"
              enctype="multipart/form-data"
              x-target="preview-panel"
              @ajax:success="saved = true; setTimeout(() => saved = false, 2000)"
              @input.debounce.600ms="if (!hasFileUpload) $el.requestSubmit()"
              novalidate>
            @csrf
            @method('PUT')

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
                    <div class="join w-full">
                        <span class="join-item btn btn-sm btn-disabled no-animation">/blog/</span>
                        <input type="text" name="slug" x-model="slug"
                               class="join-item input input-bordered input-sm flex-1 @error('slug') input-error @enderror"
                               placeholder="auto-generated">
                    </div>
                    @error('slug')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
                </fieldset>

                {{-- Content --}}
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">Content (Markdown)</legend>
                    <textarea name="content" x-model="content"
                              @blur="generateSummary()"
                              class="textarea textarea-bordered w-full h-64 font-mono text-sm @error('content') textarea-error @enderror"
                              placeholder="# Write your article here...">{{ old('content', $article->content) }}</textarea>
                    @error('content')
                        <span class="text-error text-sm">{{ $message }}</span>
                    @enderror
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
                    <select name="status" class="select select-bordered w-full @error('status') select-error @enderror">
                        <option value="draft" {{ old('status', $article->status->value) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $article->status->value) === 'published' ? 'selected' : '' }}>Published</option>
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

            {{-- Sticky bottom button --}}
            <div class="sticky bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-base-100 from-60% to-transparent pt-8">
                {{-- Upload + View: shown when file is selected (native form submit, bypasses AJAX) --}}
                <button x-show="hasFileUpload" type="button" x-cloak
                        @click="let form = $el.closest('form'); form.removeAttribute('x-target'); form.submit()"
                        class="btn btn-primary w-full gap-2">
                    <i class="ph ph-upload-simple"></i>
                    Upload Image and View Live
                </button>

                {{-- View Live: shown normally (navigates to frontend) --}}
                <a x-show="!hasFileUpload"
                   href="{{ route('article.show', $article->slug) }}"
                   class="btn btn-primary w-full gap-2">
                    <i class="ph ph-arrow-square-out"></i>
                    View Live
                </a>
            </div>
        </form>
    </div>

</x-layouts.customizer>
