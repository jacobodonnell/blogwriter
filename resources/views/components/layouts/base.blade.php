<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @if($darkMode)
        x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
        x-init="
            $watch('darkMode', value => {
                localStorage.setItem('darkMode', value);
                document.documentElement.setAttribute('data-theme', value ? 'dark' : 'light');
            });
            document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
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
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#ffffff">

    <title>{{ $title ?: config('app.name', 'BlogWriter') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

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