<?php

namespace App\View\Components\Layouts;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Base extends Component
{
    public string $themeLight;

    public string $themeDark;

    public function __construct(
        public string $title = '',
        public bool $darkMode = false,
        public string $iconWeight = 'regular',
        public string $themeFont = '',
        public string $jsEntry = 'resources/js/app-guest.js',
    ) {
        $this->themeLight = setting('theme_light', config('appearance.defaults.theme_light', 'lofi'));
        $this->themeDark = setting('theme_dark', config('appearance.defaults.theme_dark', 'dracula'));

        if ($this->themeFont === '') {
            $this->themeFont = setting('theme_font', config('appearance.defaults.theme_font', 'noto-sans'));
        }
    }

    public function render(): View
    {
        return view('components.layouts.base');
    }
}
