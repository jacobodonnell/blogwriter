<x-layouts.base 
    :title="(isset($title) ? $title . ' - ' : '') . config('app.name', 'BlogWriter')"
    :dark-mode="false"
    icon-weight="regular">

    <div class="min-h-screen bg-base-200 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</x-layouts.base>