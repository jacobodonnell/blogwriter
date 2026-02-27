<x-layouts.base 
    :title="(!empty($title) ? $title . ' - ' : '') . config('app.name', 'BlogWriter')"
    :dark-mode="false"
    icon-weight="regular">

    <x-demo-banner />
    <div class="min-h-screen bg-base-200 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</x-layouts.base>