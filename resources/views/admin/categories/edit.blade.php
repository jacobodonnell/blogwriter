<x-layouts.admin title="Edit Category">

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Edit Category</h1>
                <p class="text-base-content/70 mt-1">Update category details.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">
                Back to Categories
            </a>
        </div>

        {{-- Form --}}
        <div class="card bg-base-100 shadow max-w-2xl">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name"
                            class="input input-bordered @error('name') input-error @enderror"
                            value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Slug</span>
                            <span class="label-text-alt">URL-friendly identifier</span>
                        </label>
                        <input type="text" name="slug"
                            class="input input-bordered @error('slug') input-error @enderror"
                            value="{{ old('slug', $category->slug) }}" required>
                        @error('slug')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Parent Category</span>
                        </label>
                        <select name="parent_id" class="select select-bordered @error('parent_id') select-error @enderror">
                            <option value="">None (Root)</option>
                            @foreach($allCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea name="description"
                            class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="card-actions mt-6">
                        <button type="submit" class="btn btn-primary">Update Category</button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin>
