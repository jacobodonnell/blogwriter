{{-- Shared authenticated sidebar shell used by admin.blade.php and public.blade.php --}}
<div x-cloak x-data="sidebar" class="flex flex-col min-h-screen">

    @include('components.layouts.partials.app-header')

    <div class="flex flex-1 relative">
        @include('components.layouts.partials.app-sidebar')

        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
            <x-flash-messages />
            {{ $slot }}
        </div>
    </div>

</div>
