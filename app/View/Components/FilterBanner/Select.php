<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;

final class Select extends FilterField
{
    /**
     * @param  array<string, string>  $options
     */
    public function __construct(
        string $name,
        string $label = '',
        public array $options = [],
        public string $emptyLabel = 'All',
        bool $auth = false,
        int $colspan = 1,
    ) {
        parent::__construct($label, $name, $auth, $colspan);
    }

    public function render(): View
    {
        return view('components.filter-banner.select');
    }
}
