@props(['categories', 'label' => 'Category', 'auth' => false, 'colspan' => 1])

@if($auth)
    @auth
        <div class="sm:col-span-{{ $colspan }}">
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <x-category-select :categories="$categories"
                name="category" emptyLabel="All Categories"
                :selected="request('category')" :useSlug="true"
                @change="$el.form.requestSubmit()" />
        </div>
    @endauth
@else
    <div class="sm:col-span-{{ $colspan }}">
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <x-category-select :categories="$categories"
            name="category" emptyLabel="All Categories"
            :selected="request('category')" :useSlug="true"
            @change="$el.form.requestSubmit()" />
    </div>
@endif
