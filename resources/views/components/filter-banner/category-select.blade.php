<div @class([$colspanClass])>
    <label class="label"><span class="label-text">{{ $label }}</span></label>
    <x-category-select :categories="$categories"
        name="category" emptyLabel="All Categories"
        :selected="$selected ?? request('category')" :useSlug="true"
        :flat="$flat"
        data-test="filter-category"
        @change="$el.form.requestSubmit()" />
</div>
