@props(['categories', 'label' => 'Category', 'auth' => false, 'colspan' => 1])

@if($auth)
    @auth
        <div @class([
            'sm:col-span-2' => $colspan == 2,
            'sm:col-span-3' => $colspan == 3,
            'sm:col-span-4' => $colspan == 4,
        ])>
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <x-category-select :categories="$categories"
                name="category" emptyLabel="All Categories"
                :selected="request('category')" :useSlug="true"
                @change="$el.form.requestSubmit()" />
        </div>
    @endauth
@else
    <div @class([
        'sm:col-span-2' => $colspan == 2,
        'sm:col-span-3' => $colspan == 3,
        'sm:col-span-4' => $colspan == 4,
    ])>
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <x-category-select :categories="$categories"
            name="category" emptyLabel="All Categories"
            :selected="request('category')" :useSlug="true"
            @change="$el.form.requestSubmit()" />
    </div>
@endif
