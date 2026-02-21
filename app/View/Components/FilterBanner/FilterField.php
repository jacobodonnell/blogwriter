<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

abstract class FilterField extends Component
{
    public string $colspanClass;

    public function __construct(
        public string $label = '',
        public string $name = '',
        public bool $auth = false,
        public int $colspan = 1,
    ) {
        $this->colspanClass = match ($this->colspan) {
            2 => 'sm:col-span-2',
            3 => 'sm:col-span-3',
            4 => 'sm:col-span-4',
            default => '',
        };
    }

    final public function shouldRender(): bool
    {
        return ! ($this->auth && Auth::guest());
    }
}
