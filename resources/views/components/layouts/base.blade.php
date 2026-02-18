<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @if($darkMode)
        x-data="{
            themeMode: localStorage.getItem('themeMode') || 'system',
            systemDark: window.matchMedia('(prefers-color-scheme: dark)').matches,
            get resolvedTheme() {
                if (this.themeMode === 'dark') return '{{ $themeDark }}';
                if (this.themeMode === 'light') return '{{ $themeLight }}';
                return this.systemDark ? '{{ $themeDark }}' : '{{ $themeLight }}';
            },
            cycleTheme() {
                const modes = ['light', 'dark', 'system'];
                this.themeMode = modes[(modes.indexOf(this.themeMode) + 1) % 3];
            }
        }"
        x-init="
            $watch('themeMode', value => {
                localStorage.setItem('themeMode', value);
                document.documentElement.setAttribute('data-theme', resolvedTheme);
            });
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                systemDark = e.matches;
                document.documentElement.setAttribute('data-theme', resolvedTheme);
            });
            document.documentElement.setAttribute('data-theme', resolvedTheme);
        "
    @endif
    >
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/icons/icon-32.png" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#ffffff">

    <title>{!! $title ?: config('app.name', 'BlogWriter') !!}</title>

    <!-- Prevent x-cloak elements from flashing before Alpine -->
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Font override from appearance settings -->
    <style>:root { --font-sans: var(--font-{{ $themeFont }}); --font-size-scale: {{ $fontSizeScale }}; }</style>

    @if($darkMode)
    <!-- Instant theme application to prevent FOUC -->
    <script>
        (function() {
            var m = localStorage.getItem('themeMode') || 'system';
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme',
                m === 'dark' ? '{{ $themeDark }}' : m === 'light' ? '{{ $themeLight }}' : (d ? '{{ $themeDark }}' : '{{ $themeLight }}')
            );
        })();
    </script>
    @endif

    <!-- Phosphor Icons -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/{{ $iconWeight }}/style.css" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- HOOK: Additional head content --}}
    {{ $head ?? '' }}

    {{-- HOOK: End of head, right before </head> --}}
    {{ $headEnd ?? '' }}
</head>
<body class="min-h-screen bg-base-200">
    {{-- HOOK: Right after <body> --}}
    {{ $bodyOpen ?? '' }}

    {{-- Main Content --}}
    {{ $slot }}

    {{-- HOOK: Right before closing </body> --}}
    {{ $bodyEnd ?? '' }}
</body>
</html>
