<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class FilterBanner extends Component
{
    public bool $hasFilters;

    public int $activeFilterCount;

    /**
     * @param  array<string>  $filterParams
     */
    public function __construct(
        public string $action,
        public string $target,
        public string $clearRoute,
        public string $persistKey = '',
        public array $filterParams = ['search', 'category', 'type', 'status', 'sort', 'content_type'],
    ) {
        $activeValues = collect($this->filterParams)
            ->filter(fn (string $param): bool => request()->filled($param));

        $this->activeFilterCount = $activeValues->count();
        $this->hasFilters = $this->activeFilterCount > 0;
    }

    public function render(): View
    {
        return view('components.filter-banner.filter-banner');
    }
}
