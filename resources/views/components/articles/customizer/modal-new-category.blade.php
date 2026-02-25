@props(['categories'])

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
