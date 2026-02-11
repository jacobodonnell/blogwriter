<x-layouts.admin>
    <x-slot:title>{{ $article->exists ? 'Edit Article' : 'New Article' }}</x-slot:title>

    <div class="space-y-6" 
         x-data="articleForm()"
         x-init="
            title = @js(old('title', $article->title ?? ''));
            slug = @js(old('slug', $article->slug ?? ''));
            content = @js(old('content', $article->content ?? ''));
            summary = @js(old('summary', $article->summary ?? ''));
         ">
        <script>
            function articleForm() {
                return {
                    title: '',
                    slug: '',
                    content: '',
                    summary: '',
                    activeTab: 'edit',
                    
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

                    markdownPreview() {
                        if (!this.content) return '<p class=\"text-base-content/50 italic\">No content to preview...</p>';
                        
                        return this.content
                            .replace(/^### (.*$)/gim, '<h3 class=\"text-xl font-bold mb-2\">$1</h3>')
                            .replace(/^## (.*$)/gim, '<h2 class=\"text-2xl font-bold mb-3\">$1</h2>')
                            .replace(/^# (.*$)/gim, '<h1 class=\"text-3xl font-bold mb-4\">$1</h1>')
                            .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                            .replace(/\*(.*)\*/gim, '<em>$1</em>')
                            .replace(/`([^`]+)`/gim, '<code class=\"bg-base-300 px-1 rounded\">$1</code>')
                            .replace(/^\> (.*$)/gim, '<blockquote class=\"border-l-4 border-base-300 pl-4 italic\">$1</blockquote>')
                            .replace(/\[([^\]]+)\]\(([^)]+)\)/gim, '<a href=\"$2\" class=\"link link-primary\">$1</a>')
                            .replace(/^(\-|\*) (.*$)/gim, '<li class=\"ml-4\">$2</li>')
                            .replace(/\n/gim, '<br>');
                    }
                }
            }
        </script>

        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ $article->exists ? 'Edit Article' : 'New Article' }}</h1>
                <p class="text-base-content/70 mt-1">
                    {{ $article->exists ? 'Update your article.' : 'Create a new blog article.' }}
                </p>
            </div>
            <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost btn-sm gap-2">
                <i class="ph ph-arrow-left text-lg"></i>
                Back
            </a>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="ph ph-x-circle text-xl"></i>
                <div class="flex flex-col">
                    @foreach ($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" enctype="multipart/form-data"
              action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" 
              class="space-y-6">
            @csrf
            @if($article->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {{-- Main Content Column --}}
                <div class="xl:col-span-2 space-y-6">
                    {{-- Content Card --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body space-y-4">
                            {{-- Title --}}
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Article Title</legend>
                                <input type="text" 
                                       name="title" 
                                       x-model="title" 
                                       @blur="generateSlug()"
                                       class="input input-bordered w-full @error('title') input-error @enderror" 
                                       placeholder="Enter article title"
                                       required>
                                @error('title')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>

                            {{-- Slug --}}
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">URL Slug</legend>
                                <div class="join w-full">
                                    <span class="join-item btn btn-disabled no-animation">/blog/</span>
                                    <input type="text" 
                                           name="slug" 
                                           x-model="slug"
                                           class="join-item input input-bordered flex-1 @error('slug') input-error @enderror" 
                                           placeholder="auto-generated-from-title"
                                           required>
                                </div>
                                <p class="text-xs text-base-content/50 mt-1">Leave empty to auto-generate from title</p>
                                @error('slug')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>

                            {{-- Content with Tabs --}}
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Content (Markdown)</legend>
                                
                                {{-- Tabs --}}
                                <div class="tabs tabs-boxed mb-2 bg-base-200 p-1">
                                    <button type="button" 
                                            @click="activeTab = 'edit'" 
                                            :class="{ 'tab-active': activeTab === 'edit' }" 
                                            class="tab gap-2">
                                        <i class="ph ph-pencil-simple"></i>
                                        Edit
                                    </button>
                                    <button type="button" 
                                            @click="activeTab = 'preview'" 
                                            :class="{ 'tab-active': activeTab === 'preview' }" 
                                            class="tab gap-2">
                                        <i class="ph ph-eye"></i>
                                        Preview
                                    </button>
                                </div>

                                {{-- Edit Tab --}}
                                <div x-show="activeTab === 'edit'" x-cloak>
                                    <textarea name="content" 
                                              x-model="content" 
                                              @blur="generateSummary()"
                                              class="textarea textarea-bordered w-full h-96 font-mono text-sm @error('content') textarea-error @enderror" 
                                              placeholder="# Write your article here..."
                                              required>{{ old('content', $article->content) }}</textarea>
                                </div>

                                {{-- Preview Tab --}}
                                <div x-show="activeTab === 'preview'" 
                                     x-cloak 
                                     class="prose max-w-none p-4 bg-base-200 rounded-lg min-h-[24rem] max-h-96 overflow-y-auto">
                                    <div x-html="markdownPreview()"></div>
                                </div>

                                @error('content')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>

                            {{-- Summary --}}
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">Summary / Excerpt</legend>
                                <textarea name="summary" 
                                          x-model="summary"
                                          class="textarea textarea-bordered w-full h-24 @error('summary') textarea-error @enderror" 
                                          placeholder="Brief summary (auto-generated if empty)">{{ old('summary', $article->summary) }}</textarea>
                                <p class="text-xs text-base-content/50 mt-1">First 255 characters of content if left empty</p>
                                @error('summary')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Column --}}
                <div class="space-y-6">
                    {{-- Publish Settings --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-lg flex items-center gap-2">
                                <i class="ph ph-gear"></i>
                                Publish Settings
                            </h3>

                            {{-- Status --}}
                            <fieldset class="fieldset mt-4">
                                <legend class="fieldset-legend">Status</legend>
                                <select name="status" class="select select-bordered w-full @error('status') select-error @enderror">
                                    <option value="draft" {{ old('status', $article?->status?->value) === 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                    <option value="published" {{ old('status', $article?->status?->value) === 'published' ? 'selected' : '' }}>✅ Published</option>
                                    <option value="hidden" {{ old('status', $article?->status?->value) === 'hidden' ? 'selected' : '' }}>👁️ Hidden</option>
                                </select>
                                @error('status')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>

                            {{-- Categories --}}
                            <fieldset class="fieldset mt-4">
                                <legend class="fieldset-legend">Categories</legend>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-2 bg-base-200 rounded-lg @error('categories') border border-error @enderror">
                                    @forelse($categories ?? [] as $category)
                                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-300 p-1 rounded transition-colors">
                                            <input type="checkbox" 
                                                   name="categories[]" 
                                                   value="{{ $category->id }}" 
                                                   class="checkbox checkbox-sm"
                                                   {{ in_array($category->id, old('categories', $article->categories->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $category->name }}</span>
                                        </label>
                                    @empty
                                        <p class="text-sm text-base-content/50 italic p-2">No categories available. <a href="{{ route('admin.categories.index') }}" class="link link-primary">Create one</a></p>
                                    @endforelse
                                </div>
                                @error('categories')
                                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </fieldset>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-primary w-full mt-6 gap-2">
                                <i class="ph ph-check"></i>
                                {{ $article->exists ? 'Update Article' : 'Create Article' }}
                            </button>
                        </div>
                    </div>

                    {{-- Featured Image --}}
                    @php
                        $initialImageUrl = old('featured_image', $article->featured_image ?? '');
                        $initialOriginalUrl = $article->featured_image ?? '';
                        $initialIsRemoved = old('remove_featured_image') ? true : false;
                        $initialActiveTab = $initialImageUrl && !str_starts_with($initialImageUrl, 'http') ? 'upload_file' : 'external_url';
                        $storageUrl = Storage::disk('public')->url('');
                    @endphp
                    <div class="card bg-base-100 shadow-sm"
                         x-data="featuredImage({{ json_encode($initialImageUrl, JSON_UNESCAPED_SLASHES) }}, {{ json_encode($initialOriginalUrl, JSON_UNESCAPED_SLASHES) }}, {{ json_encode($initialIsRemoved) }}, {{ json_encode($initialActiveTab) }}, {{ json_encode($storageUrl, JSON_UNESCAPED_SLASHES) }})">
                        <div class="card-body">
                            <h3 class="card-title text-lg flex items-center gap-2">
                                <i class="ph ph-image"></i>
                                Featured Image
                            </h3>

                            {{-- Hidden checkbox for server state --}}
                            <input type="hidden" x-ref="originalUrl" :value="originalUrl">
                            <input type="checkbox" name="remove_featured_image" value="1" x-model="isRemoved" class="hidden">

                            {{-- DaisyUI Radio Tabs --}}
                            <div class="tabs tabs-lifted mb-4" role="tablist">
                                <input type="radio"
                                       name="image_tab"
                                       value="external_url"
                                       class="tab"
                                       aria-label="External URL"
                                       role="tab"
                                       tabindex="0"
                                       :checked="activeTab === 'external_url'"
                                       @click="setTab('external_url')"
                                       @keydown="if ($event.key === 'ArrowLeft') setTab('upload_file'); if ($event.key === 'ArrowRight') setTab('upload_file')">
                                
                                <input type="radio"
                                       name="image_tab"
                                       value="upload_file"
                                       class="tab"
                                       aria-label="Upload File"
                                       role="tab"
                                       tabindex="0"
                                       :checked="activeTab === 'upload_file'"
                                       @click="setTab('upload_file')"
                                       @keydown="if ($event.key === 'ArrowLeft') setTab('external_url'); if ($event.key === 'ArrowRight') setTab('external_url')">
                            </div>

                             {{-- Image Preview Area --}}
                             <div x-show="hasImage" x-transition class="relative mb-4" role="tabpanel">
                                 <figure class="relative">
                                    {{-- Badge overlay --}}
                                    <div class="absolute top-2 left-2 z-10">
                                        <span x-show="imageUrl && imageUrl.startsWith('http')" 
                                              class="badge badge-primary">
                                            🔗 External URL
                                        </span>
                                        <span x-show="filePreview || (imageUrl && !imageUrl.startsWith('http'))" 
                                              class="badge badge-secondary">
                                            📤 Uploaded File
                                        </span>
                                    </div>
                                    
{{-- Delete button --}}
                                      <button type="button" 
                                              @click="removeImage()"
                                              class="btn btn-circle btn-error btn-sm absolute top-2 right-2 z-10"
                                              aria-label="Remove featured image">
                                          <i class="ph ph-x text-lg"></i>
                                      </button>
                                    
                                    {{-- Preview image --}}
                                    <img :src="previewUrl" 
                                         alt="Featured image preview"
                                         class="w-full h-48 object-cover rounded-lg">
                                    
                                     {{-- File info --}}
                                     <div x-show="fileName" class="mt-2 text-sm text-gray-600">
                                         <span x-text="fileName"></span>
                                         <span x-show="fileSize" x-text="fileSize" class="text-gray-400"></span>
                                     </div>
                                </figure>
                            </div>

                             {{-- Undo Button --}}
                             <div x-show="isRemoved && originalUrl" x-transition class="mb-4">
<button type="button"
                                          @click="undoDelete()"
                                          class="btn btn-ghost btn-sm"
                                          aria-label="Restore featured image">
                                     <i class="ph ph-arrow-u-up-left mr-1"></i>
                                     Undo
                                 </button>
                             </div>

                            {{-- Error Message --}}
                            <div x-show="errorMessage" x-transition class="alert alert-error mb-4">
                                <i class="ph ph-warning-circle text-xl"></i>
                                <span x-text="errorMessage"></span>
                            </div>

                            {{-- External URL Tab Content --}}
                            <div x-show="activeTab === 'external_url'" x-transition role="tabpanel">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-sm">Image URL</legend>
                                    <input type="url"
                                           name="featured_image"
                                           x-model="imageUrl"
                                           @input="clearFilePreview()"
                                           :disabled="activeTab !== 'external_url'"
                                           class="input input-bordered w-full text-sm @error('featured_image') input-error @enderror"
                                           placeholder="https://example.com/image.jpg">
                                    @error('featured_image')
                                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </fieldset>
                            </div>

                            {{-- Upload File Tab Content --}}
                            <div x-show="activeTab === 'upload_file'" x-transition role="tabpanel">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-sm">Upload Image</legend>
<input type="file"
                                            name="featured_image_file"
                                            x-ref="fileInput"
                                            @change="handleFileSelect($event)"
                                            class="file-input file-input-bordered w-full file-input-sm @error('featured_image_file') input-error @enderror"
                                            accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">
                                    <p class="text-xs text-gray-500 mt-1">Maximum file size: 2MB. Supported formats: JPG, JPEG, PNG, WebP, GIF</p>
                                    @error('featured_image_file')
                                        <span class="text-error text-sm mt-1">{{ $message }}</span>
                                    @enderror
                                </fieldset>
                            </div>
                        </div>

                        <script>
                            function featuredImage(initialImageUrl, initialOriginalUrl, initialIsRemoved, initialActiveTab, storageUrl) {
                                return {
                                    activeTab: initialActiveTab || 'external_url',
                                    imageUrl: initialImageUrl || '',
                                    originalUrl: initialOriginalUrl || '',
                                    filePreview: null,
                                    fileName: null,
                                    fileSize: null,
                                    isRemoved: initialIsRemoved || false,
                                    errorMessage: null,
                                    storageUrl: storageUrl || '',
                                    maxSize: 2097152, // 2MB in bytes
                                    
                                    get hasImage() {
                                        return !this.isRemoved && (this.imageUrl || this.filePreview);
                                    },
                                    
                                    get previewUrl() {
                                        if (this.filePreview) {
                                            return this.filePreview;
                                        }
                                        if (this.imageUrl) {
                                            if (this.imageUrl.startsWith('http')) {
                                                return this.imageUrl;
                                            } else {
                                                return this.storageUrl + this.imageUrl;
                                            }
                                        }
                                        return '';
                                    },
                                    
                                    handleFileSelect(event) {
                                        const file = event.target.files[0];
                                        if (!file) return;
                                        
                                        // Client-side validation
                                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                                        if (!validTypes.includes(file.type)) {
                                            this.errorMessage = 'Please select a valid image file (jpg, jpeg, png, webp, gif).';
                                            event.target.value = '';
                                            return;
                                        }
                                        
                                        // File size validation (2MB = 2097152 bytes)
                                        if (file.size > this.maxSize) {
                                            this.errorMessage = 'File is too large. Maximum size is 2MB (2048 KB).';
                                            event.target.value = '';
                                            return;
                                        }
                                        
                                        this.errorMessage = null;
                                        this.filePreview = URL.createObjectURL(file);
                                        this.fileName = file.name;
                                        const sizeInMB = file.size / 1024 / 1024;
                                        const sizeInKB = file.size / 1024;
                                        if (sizeInMB >= 1) {
                                            this.fileSize = sizeInMB.toFixed(2) + ' MB';
                                        } else {
                                            this.fileSize = Math.round(sizeInKB) + ' KB';
                                        }
                                        this.isRemoved = false;
                                    },
                                    
                                    removeImage() {
                                        this.isRemoved = true;
                                        this.filePreview = null;
                                        this.imageUrl = '';
                                        this.fileName = null;
                                        this.fileSize = null;
                                        this.errorMessage = null;
                                        // Clear file input
                                        if (this.$refs.fileInput) {
                                            this.$refs.fileInput.value = '';
                                        }
                                    },
                                    
                                    undoDelete() {
                                        this.isRemoved = false;
                                        this.imageUrl = this.originalUrl;
                                        this.filePreview = null;
                                        this.fileName = null;
                                        this.fileSize = null;
                                        // Clear file input
                                        if (this.$refs.fileInput) {
                                            this.$refs.fileInput.value = '';
                                        }
                                    },
                                    
                                    clearFilePreview() {
                                        this.filePreview = null;
                                        this.fileName = null;
                                        this.fileSize = null;
                                        this.isRemoved = false;
                                        if (this.$refs.fileInput) {
                                            this.$refs.fileInput.value = '';
                                        }
                                    },
                                    
                                    setTab(tab) {
                                        this.activeTab = tab;
                                        this.errorMessage = null;
                                    },
                                    
                                    // Alias methods for test compatibility
                                    previewFile(event) {
                                        // Alias for handleFileSelect - for test compatibility
                                        this.handleFileSelect(event);
                                    },
                                    
                                    deleteImage() {
                                        // Alias for removeImage - for test compatibility
                                        this.removeImage();
                                    },
                                    
                                    restoreImage() {
                                        // Alias for undoDelete - for test compatibility
                                        this.undoDelete();
                                    }
                                }
                            }
                        </script>
                    </div>

                    {{-- SEO Meta --}}
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-lg flex items-center gap-2">
                                <i class="ph ph-magnifying-glass"></i>
                                SEO Settings
                            </h3>

                            @php
                                $meta = old('meta', $article->meta ?? []);
                            @endphp

                            <fieldset class="fieldset mt-4">
                                <legend class="fieldset-legend text-sm">Meta Title</legend>
                                <input type="text" 
                                       name="meta[meta_title]" 
                                       class="input input-bordered w-full text-sm" 
                                       value="{{ $meta['meta_title'] ?? '' }}"
                                       placeholder="Custom title for search engines">
                            </fieldset>

                            <fieldset class="fieldset mt-4">
                                <legend class="fieldset-legend text-sm">Meta Description</legend>
                                <textarea name="meta[meta_description]" 
                                          class="textarea textarea-bordered w-full h-20 text-sm" 
                                          placeholder="Brief description for search results">{{ $meta['meta_description'] ?? '' }}</textarea>
                            </fieldset>

                            <fieldset class="fieldset mt-4">
                                <legend class="fieldset-legend text-sm">OG Image URL</legend>
                                <input type="url" 
                                       name="meta[og_image]" 
                                       class="input input-bordered w-full text-sm" 
                                       value="{{ $meta['og_image'] ?? '' }}"
                                       placeholder="https://example.com/og-image.jpg">
                            </fieldset>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>