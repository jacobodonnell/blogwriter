<div x-data="{ name: '', slug: '' }">
    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-4">
            <i class="ph ph-warning-circle text-lg"></i>
            <ul class="list-none m-0 p-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.categories.store') }}"
          x-target="add-category-form">
        @csrf

        <div class="space-y-4">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Parent Category</legend>
                <select name="parent_id"
                        class="select select-bordered w-full">
                    <option value="">None (Root)</option>
                    @foreach ($allCategories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('parent_id', $parent?->id) == $cat->id ? 'selected' : '' }}>
                            {{ str_repeat('— ', $cat->depth) }}{{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Name</legend>
                <input type="text"
                       name="name"
                       x-model="name"
                       @blur="if (!slug) { slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') }"
                       class="input input-bordered w-full {{ $errors->has('name') ? 'input-error' : '' }}"
                       placeholder="Category name"
                       value="{{ old('name') }}"
                       required>
                @error('name')
                    <p class="fieldset-label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Slug <span class="text-base-content/50">(optional)</span></legend>
                <input type="text"
                       name="slug"
                       x-model="slug"
                       class="input input-bordered w-full {{ $errors->has('slug') ? 'input-error' : '' }}"
                       placeholder="auto-generated"
                       value="{{ old('slug') }}">
                @error('slug')
                    <p class="fieldset-label text-error">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Description <span class="text-base-content/50">(optional)</span></legend>
                <input type="text"
                       name="description"
                       class="input input-bordered w-full"
                       placeholder="Brief description"
                       value="{{ old('description') }}">
            </fieldset>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-ghost" @click="$dispatch('modal:close')">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    {{ $parent ? 'Add Subcategory' : 'Add Category' }}
                </button>
            </div>
        </div>
    </form>
</div>
