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
        'inter' => 'Inter',
        'geist-sans' => 'Geist',
        'open-sans' => 'Open Sans',
        'roboto' => 'Roboto',
        'fira-sans' => 'Fira Sans',
        'nunito' => 'Nunito',
        'poppins' => 'Poppins',
        'work-sans' => 'Work Sans',

        // Serif
        'noto-serif' => 'Noto Serif',
        'merriweather' => 'Merriweather',
        'crimson-pro' => 'Crimson Pro',
        'lora' => 'Lora',
        'playfair-display' => 'Playfair Display',
        'source-serif-4' => 'Source Serif 4',

        // Admin UI
        'instrument-sans' => 'Instrument Sans',

        // Monospace
        'fira-code' => 'Fira Code',
        'jetbrains-mono' => 'JetBrains Mono',
        'source-code-pro' => 'Source Code Pro',

    ],

    'font_categories' => [
        'Sans-Serif' => ['noto-sans', 'inter', 'geist-sans', 'open-sans', 'roboto', 'fira-sans', 'nunito', 'poppins', 'work-sans'],
        'Serif' => ['noto-serif', 'merriweather', 'crimson-pro', 'lora', 'playfair-display', 'source-serif-4'],
        'Admin UI' => ['instrument-sans'],
        'Monospace' => ['fira-code', 'jetbrains-mono', 'source-code-pro'],
    ],

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
