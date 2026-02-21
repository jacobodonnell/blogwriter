<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <x-category-select :categories="$categories"
        name="category" emptyLabel="All Categories"
        :selected="request('category')" :useSlug="true"
        data-test="filter-category"
        @change="$el.form.requestSubmit()" />
</div>
