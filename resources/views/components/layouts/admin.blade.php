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

    <div x-cloak x-data="sidebar"
        class="flex flex-col min-h-screen">

        {{-- Full-width Header --}}
        @include('components.layouts.partials.app-header')

        {{-- Body: Sidebar + Content --}}
        <div class="flex flex-1 relative">
            {{-- Sidebar --}}
            @include('components.layouts.partials.app-sidebar')

            {{-- Main Content --}}
            <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
                {{-- Flash Messages --}}
                @if (session('success'))
                    <div x-data="{ show: true }"
                         x-init="setTimeout(() => show = false, 5000)"
                         x-show="show"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed top-4 right-4 z-50 alert alert-success shadow-lg">
                        <i class="ph ph-check-circle text-xl"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         class="fixed top-4 right-4 z-50 alert alert-error shadow-lg">
                        <i class="ph ph-x-circle text-xl"></i>
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="btn btn-ghost btn-xs">
                            <i class="ph ph-x"></i>
                        </button>
                    </div>
                @endif

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
            </div>
        </div>
    </div>
</x-layouts.base>
