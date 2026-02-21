<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;

final class PerPage extends FilterField
{
    /**
     * @param  array<int>  $options
     */
    public function __construct(
        string $label = 'Per Page',
        string $name = 'perPage',
        public array $options = [10, 20, 50, 100],
        public int $default = 20,
        bool $auth = false,
        int $colspan = 1,
    ) {
        parent::__construct($label, $name, $auth, $colspan);
    }

    public function render(): View
    {
        return view('components.filter-banner.per-page');
    }
}
