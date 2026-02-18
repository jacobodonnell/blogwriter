@props([
    'categories',
    'selected' => null,
    'name' => 'category_id',
    'emptyLabel' => 'No Category',
    'useSlug' => false,
])

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}>
    <option value="">{{ $emptyLabel }}</option>
    @foreach($categories as $rootCat)
        @php $rootVal = $useSlug ? $rootCat->slug : $rootCat->id; @endphp
        <option value="{{ $rootVal }}" {{ old($name, $selected) == $rootVal ? 'selected' : '' }}>
            {{ $rootCat->name }}
        </option>
        @foreach($rootCat->children as $childCat)
            @php $childVal = $useSlug ? $childCat->slug : $childCat->id; @endphp
            <option value="{{ $childVal }}" {{ old($name, $selected) == $childVal ? 'selected' : '' }}>
                &nbsp;&nbsp;└ {{ $childCat->name }}
            </option>
        @endforeach
    @endforeach
</select>
