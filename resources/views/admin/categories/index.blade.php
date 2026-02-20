<x-layouts.admin title="Categories">
    <x-slot:breadcrumb>
        <li>Categories</li>
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">{{ $parent ? $parent->name : 'Categories' }}</h1>
                @if($breadcrumbs->isNotEmpty())
                    <nav class="text-sm breadcrumbs mt-1">
                        <ul>
                            <li>
                                <a href="{{ route('admin.categories.index') }}" class="link link-hover">
                                    Categories
                                </a>
                            </li>
                            @foreach($breadcrumbs as $crumb)
                                @if(!$loop->last)
                                    @php
                                        $crumbPath = $breadcrumbs->slice(0, $loop->index + 1)->pluck('slug')->implode('/');
                                    @endphp
                                    <li>
                                        <a href="{{ route('admin.categories.children', $crumbPath) }}" class="link link-hover">
                                            {{ $crumb->name }}
                                        </a>
                                    </li>
                                @else
                                    <li class="text-base-content/60">{{ $crumb->name }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                @else
                    <p class="text-base-content/70 mt-1">Manage article categories.</p>
                @endif
            </div>
            <button
                class="btn btn-primary btn-sm shrink-0"
                onclick="document.getElementById('add-category-modal').showModal()">
                <i class="ph ph-plus text-base"></i>
                {{ $parent ? 'Add Subcategory' : 'Add Category' }}
            </button>
        </div>

        {{-- Categories List --}}
        @include('admin.categories._table')
    </div>

    {{-- Add Category Modal --}}
    <x-editor-modal
        id="add-category-modal"
        x-init
        @category:created.window="$el.close()"
        @modal:close.window="$el.close()"
        :title="$parent ? 'Add Subcategory in ' . $parent->name : 'Add New Category'">
        <div id="add-category-form">
            @include('admin.categories._add-form')
        </div>
    </x-editor-modal>
</x-layouts.admin>
