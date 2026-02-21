@props([
    'categories',
    'selected' => null,
    'name' => 'category_id',
    'emptyLabel' => 'No Category',
    'useSlug' => false,
    'flat' => false,
])

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}>
    <option value="">{{ $emptyLabel }}</option>
    @if($flat)
        @foreach($categories as $cat)
            @php
                $val = $useSlug ? $cat->slug : $cat->id;
                $label = $cat->depth > 0 ? $cat->sort_path : $cat->name;
            @endphp
            <option value="{{ $val }}" {{ old($name, $selected) == $val ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    @else
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
    @endif
</select>
