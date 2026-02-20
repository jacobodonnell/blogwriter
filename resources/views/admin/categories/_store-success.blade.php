<div id="add-category-form">
    <div x-init="
        $dispatch('category:created');
        $dispatch('toast:show', { message: 'Category created successfully.', type: 'success' })
    "></div>
    @include('admin.categories._add-form')
</div>
