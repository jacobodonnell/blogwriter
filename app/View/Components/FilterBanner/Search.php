<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;

final class Search extends FilterField
{
    public function __construct(
        string $label = 'Search',
        string $name = 'search',
        public string $placeholder = 'Search...',
        bool $auth = false,
        int $colspan = 1,
    ) {
        parent::__construct($label, $name, $auth, $colspan);
    }

    public function render(): View
    {
        return view('components.filter-banner.search');
    }
}
