<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

final class CategorySelect extends FilterField
{
    public function __construct(
        public Collection $categories,
        public ?string $selected = null,
        public bool $flat = false,
        string $label = 'Category',
        string $name = 'category',
        bool $auth = false,
        int $colspan = 1,
    ) {
        parent::__construct($label, $name, $auth, $colspan);
    }

    public function render(): View
    {
        return view('components.filter-banner.category-select');
    }
}
