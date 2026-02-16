<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | DaisyUI Themes
    |--------------------------------------------------------------------------
    |
    | All 35 built-in DaisyUI 5 themes, split by color scheme.
    | Dark themes have `color-scheme: dark` in DaisyUI source.
    |
    */

    'themes_light' => [
        'light',
        'cupcake',
        'bumblebee',
        'emerald',
        'corporate',
        'retro',
        'cyberpunk',
        'valentine',
        'garden',
        'lofi',
        'pastel',
        'fantasy',
        'wireframe',
        'cmyk',
        'autumn',
        'acid',
        'lemonade',
        'winter',
        'nord',
        'caramellatte',
        'silk',
    ],

    'themes_dark' => [
        'dark',
        'synthwave',
        'halloween',
        'forest',
        'aqua',
        'black',
        'luxury',
        'dracula',
        'business',
        'night',
        'coffee',
        'dim',
        'sunset',
        'abyss',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Curated font selection grouped by category.
    | Keys are CSS variable suffixes (e.g. 'noto-sans' → --font-noto-sans).
    |
    */

    'fonts' => [
        // Sans-Serif
        'noto-sans' => 'Noto Sans',
        'nunito' => 'Nunito',
        'inter' => 'Inter',
        'poppins' => 'Poppins',
        'work-sans' => 'Work Sans',

        // Admin UI
        'instrument-sans' => 'Instrument Sans',

        // Monospace
        'jetbrains-mono' => 'JetBrains Mono',

        // Pixel / Retro
        'press-start-2p' => 'Press Start 2P',
        'pixelify-sans' => 'Pixelify Sans',
        'vt323' => 'VT323',
        'silkscreen' => 'Silkscreen',
    ],

    'font_categories' => [
        'Sans-Serif' => ['noto-sans', 'nunito', 'inter', 'poppins', 'work-sans'],
        'Admin UI' => ['instrument-sans'],
        'Monospace' => ['jetbrains-mono'],
        'Pixel / Retro' => ['press-start-2p', 'pixelify-sans', 'vt323', 'silkscreen'],
    ],

    'monospace_fonts' => ['jetbrains-mono', 'vt323'],

    'pixel_fonts' => ['press-start-2p', 'pixelify-sans', 'vt323', 'silkscreen'],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'theme_light' => 'lofi',
        'theme_dark' => 'dracula',
        'theme_font' => 'noto-sans',
    ],

];
