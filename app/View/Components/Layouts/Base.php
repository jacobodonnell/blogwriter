<?php

namespace App\View\Components\Layouts;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Base extends Component
{
    public string $themeLight;

    public string $themeDark;

    public float $fontSizeScale;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title = '',
        public bool $darkMode = false,
        public string $iconWeight = 'regular',
        public string $themeFont = '',
    ) {
        $this->themeLight = setting('theme_light', config('appearance.defaults.theme_light', 'lofi'));
        $this->themeDark = setting('theme_dark', config('appearance.defaults.theme_dark', 'dracula'));

        if ($this->themeFont === '') {
            $this->themeFont = setting('theme_font', config('appearance.defaults.theme_font', 'noto-sans'));
        }

        $scales = config('appearance.font_size_scales', []);
        $this->fontSizeScale = (float) ($scales[$this->themeFont] ?? 1);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.layouts.base');
    }
}
