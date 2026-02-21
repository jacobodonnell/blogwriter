@props([
    'label' => 'Sort',
    'options' => null,
    'auth' => false,
    'colspan' => 1,
])

@php
    $options ??= [
        '' => 'Newest First',
        'oldest' => 'Oldest First',
        'title_asc' => 'Title A–Z',
        'title_desc' => 'Title Z–A',
    ];
@endphp

@if($auth)
    @auth
        <div class="sm:col-span-{{ $colspan }}">
            <label class="label"><span class="label-text">{{ $label }}</span></label>
            <select name="sort" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                    @change="$el.form.requestSubmit()">
                @foreach($options as $value => $display)
                    <option value="{{ $value }}" @selected(request('sort') === (string) $value)>{{ $display }}</option>
                @endforeach
            </select>
        </div>
    @endauth
@else
    <div class="sm:col-span-{{ $colspan }}">
        <label class="label"><span class="label-text">{{ $label }}</span></label>
        <select name="sort" {{ $attributes->merge(['class' => 'select select-bordered w-full']) }}
                @change="$el.form.requestSubmit()">
            @foreach($options as $value => $display)
                <option value="{{ $value }}" @selected(request('sort') === (string) $value)>{{ $display }}</option>
            @endforeach
        </select>
    </div>
@endif
