@props([
    'title' => '',
    'pageTitle' => '',
    'pageSubtitle' => '',
])

<x-layouts.base
    :title="$title ? $title . ' - ' . config('app.name', 'BlogWriter') : config('app.name', 'BlogWriter')"
    :dark-mode="true"
    icon-weight="regular"
    js-entry="resources/js/app-admin.js">

    <x-layouts.app-shell>
        {{-- Breadcrumb --}}
        @isset($breadcrumb)
            <div class="breadcrumbs text-sm px-4 py-2 bg-base-100 border-b border-base-300">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    {{ $breadcrumb }}
                </ul>
            </div>
        @endisset

        {{-- Main Content Area --}}
        <main class="flex-1 p-4 lg:p-6">
            {{-- Page Title --}}
            @if($pageTitle)
                <div class="mb-6">
                    <h1 class="text-3xl font-bold">{{ $pageTitle }}</h1>
                    @if($pageSubtitle)
                        <p class="text-base-content/60 mt-1">{{ $pageSubtitle }}</p>
                    @endif
                </div>
            @endif

            {{-- Content Slot --}}
            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>

        {{-- Footer --}}
        <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-300">
            <div>
                <p class="text-sm">BlogWriter v{{ config('app.version') }}</p>
            </div>
        </footer>
    </x-layouts.app-shell>
</x-layouts.base>
