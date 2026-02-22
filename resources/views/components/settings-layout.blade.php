@props(['active'])

<x-layouts.admin title="Settings" page-title="Settings" page-subtitle="Configure your BlogWriter site.">
    <x-slot:breadcrumb>
        <li><a href="{{ route('admin.settings.profile') }}">Settings</a></li>
        <li>{{ match ($active) {
            'profile' => 'Profile',
            'site' => 'Site',
            'appearance' => 'Appearance',
            'export' => 'Export',
        } }}</li>
    </x-slot:breadcrumb>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Tab Navigation --}}
        <ul class="menu bg-base-100 shadow rounded-box w-full lg:w-56 shrink-0">
            <li>
                <a href="{{ route('admin.settings.profile') }}" class="{{ $active === 'profile' ? 'menu-active' : '' }}">
                    <i class="ph ph-user text-lg"></i>
                    Profile
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.site') }}" class="{{ $active === 'site' ? 'menu-active' : '' }}">
                    <i class="ph ph-globe text-lg"></i>
                    Site
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.appearance') }}" class="{{ $active === 'appearance' ? 'menu-active' : '' }}">
                    <i class="ph ph-palette text-lg"></i>
                    Appearance
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.export') }}" class="{{ $active === 'export' ? 'menu-active' : '' }}">
                    <i class="ph ph-download-simple text-lg"></i>
                    Export
                </a>
            </li>
        </ul>

        {{-- Content Area --}}
        <div class="flex-1 min-w-0">
            {{ $slot }}
        </div>
    </div>
</x-layouts.admin>
