<x-layouts.admin>
    @section('title', $article->exists ? 'Edit Article' : 'New Article')

    @push('scripts')
    <script>
        function articleForm() {
            return {
                title: {!! json_encode(old('title', $article->title)) !!},
                slug: {!! json_encode(old('slug', $article->slug)) !!},
                content: {!! json_encode(old('content', $article->content)) !!},
                summary: {!! json_encode(old('summary', $article->summary)) !!},
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
                }
            }
        }
    </script>
    @endpush

    <div class="space-y-6" x-data="articleForm()">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">{{ $article->exists ? 'Edit Article' : 'New Article' }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ $article->exists ? 'Update your article.' : 'Create a new blog article.' }}
                </p>
            </div>
            <a href="{{ route('admin.articles.index') }}" class="btn btn-ghost">
                Back to Articles
            </a>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" class="space-y-6">
            @csrf
            @if($article->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content Column --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Title --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Title</span>
                        </label>
                        <input type="text" name="title" x-model="title" @blur="generateSlug()" 
                            class="input input-bordered @error('title') input-error @enderror" 
                            value="{{ old('title', $article->title) }}" required>
                        @error('title')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Slug</span>
                            <span class="label-text-alt">URL-friendly identifier</span>
                        </label>
                        <input type="text" name="slug" x-model="slug" 
                            class="input input-bordered @error('slug') input-error @enderror" 
                            value="{{ old('slug', $article->slug) }}" required>
                        @error('slug')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    {{-- Content with Tabs --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Content (Markdown)</span>
                        </label>
                        
                        {{-- Tabs --}}
                        <div class="tabs tabs-boxed mb-2">
                            <button type="button" @click="activeTab = 'edit'" :class="{ 'tab-active': activeTab === 'edit' }" class="tab">
                                Edit
                            </button>
                            <button type="button" @click="activeTab = 'preview'" :class="{ 'tab-active': activeTab === 'preview' }" class="tab">
                                Preview
                            </button>
                        </div>

                        {{-- Edit Tab --}}
                        <div x-show="activeTab === 'edit'" x-cloak>
                            <textarea name="content" x-model="content" @blur="generateSummary()" 
                                class="textarea textarea-bordered h-96 font-mono text-sm @error('content') textarea-error @enderror" 
                                required>{{ old('content', $article->content) }}</textarea>
                        </div>

                        {{-- Preview Tab --}}
                        <div x-show="activeTab === 'preview'" x-cloak class="prose max-w-none p-4 bg-base-200 rounded-lg min-h-[24rem]">
                            <div x-html="$store.markdown.parse(content)"></div>
                        </div>

                        @error('content')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    {{-- Summary --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Summary</span>
                            <span class="label-text-alt">Optional - auto-generated if empty</span>
                        </label>
                        <textarea name="summary" x-model="summary" 
                            class="textarea textarea-bordered h-24 @error('summary') textarea-error @enderror">{{ old('summary', $article->summary) }}</textarea>
                        @error('summary')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                </div>

                {{-- Sidebar Column --}}
                <div class="space-y-6">
                    {{-- Publish Settings --}}
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title text-lg">Publish Settings</h3>

                            {{-- Status --}}
                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Status</span>
                                </label>
                                <select name="status" class="select select-bordered @error('status') select-error @enderror">
                                    <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="hidden" {{ old('status', $article->status) === 'hidden' ? 'selected' : '' }}>Hidden</option>
                                </select>
                                @error('status')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            {{-- Categories --}}
                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Categories</span>
                                </label>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-2 border rounded-lg">
                                    @foreach($categories as $category)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                                class="checkbox"
                                                {{ in_array($category->id, old('categories', $article->categories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <span class="text-sm">{{ $category->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('categories')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <div class="card-actions mt-6">
                                <button type="submit" class="btn btn-primary w-full">
                                    {{ $article->exists ? 'Update Article' : 'Create Article' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Featured Image --}}
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title text-lg">Featured Image</h3>

                            {{-- Current Image --}}
                            @if($article->featured_image)
                                <div class="mt-2">
                                    <img src="{{ $article->featured_image }}" alt="Featured" class="w-full h-32 object-cover rounded">
                                </div>
                            @endif

                            {{-- Image URL Input --}}
                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Image URL</span>
                                </label>
                                <input type="url" name="featured_image" 
                                    class="input input-bordered text-sm" 
                                    value="{{ old('featured_image', $article->featured_image) }}"
                                    placeholder="https://example.com/image.jpg">
                            </div>

                            {{-- Or File Upload --}}
                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Or Upload Image</span>
                                </label>
                                <input type="file" name="featured_image_file" 
                                    class="file-input file-input-bordered w-full" 
                                    accept="image/*">
                            </div>

                            @error('featured_image')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    {{-- SEO Meta --}}
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h3 class="card-title text-lg">SEO Settings</h3>

                            @php
                                $meta = old('meta', $article->meta ?? []);
                            @endphp

                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Meta Title</span>
                                </label>
                                <input type="text" name="meta[meta_title]" 
                                    class="input input-bordered text-sm" 
                                    value="{{ $meta['meta_title'] ?? '' }}"
                                    placeholder="Custom title for search engines">
                            </div>

                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Meta Description</span>
                                </label>
                                <textarea name="meta[meta_description]" 
                                    class="textarea textarea-bordered h-20 text-sm" 
                                    placeholder="Brief description for search results">{{ $meta['meta_description'] ?? '' }}</textarea>
                            </div>

                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">OG Image URL</span>
                                </label>
                                <input type="url" name="meta[og_image]" 
                                    class="input input-bordered text-sm" 
                                    value="{{ $meta['og_image'] ?? '' }}"
                                    placeholder="https://example.com/og-image.jpg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('markdown', {
                parse(content) {
                    // Simple markdown to HTML conversion for preview
                    if (!content) return '<p class="text-gray-400 italic">No content to preview...</p>';
                    
                    let html = content
                        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                        .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                        .replace(/\*(.*)\*/gim, '<em>$1</em>')
                        .replace(/`([^`]+)`/gim, '<code>$1</code>')
                        .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
                        .replace(/\[([^\]]+)\]\(([^)]+)\)/gim, '<a href="$2" class="link">$1</a>')
                        .replace(/^(\-|\*) (.*$)/gim, '<li>$2</li>')
                        .replace(/\n/gim, '<br>');
                    
                    return html;
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>