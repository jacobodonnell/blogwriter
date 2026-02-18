@props([
    'categories',
    'selected' => null,
    'name' => 'category_id',
    'emptyLabel' => 'No Category',
])

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}>
    <option value="">{{ $emptyLabel }}</option>
    @foreach($categories as $rootCat)
        <option value="{{ $rootCat->id }}" {{ old($name, $selected) == $rootCat->id ? 'selected' : '' }}>
            {{ $rootCat->name }}
        </option>
        @foreach($rootCat->children as $childCat)
            <option value="{{ $childCat->id }}" {{ old($name, $selected) == $childCat->id ? 'selected' : '' }}>
                &nbsp;&nbsp;└ {{ $childCat->name }}
            </option>
        @endforeach
    @endforeach
</select>
