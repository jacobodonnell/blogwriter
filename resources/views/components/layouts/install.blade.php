<x-layouts.base
    title="Install BlogWriter"
    :dark-mode="false"
    icon-weight="regular">

    <x-slot:head>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,700" rel="stylesheet" />
        <style>
            .font-mono { font-family: 'JetBrains Mono', monospace !important; }
        </style>
    </x-slot:head>

    <div data-theme="dark" class="min-h-screen bg-base-200 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            {{ $slot }}
        </div>
    </div>

</x-layouts.base>
