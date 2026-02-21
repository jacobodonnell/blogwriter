<?php

declare(strict_types=1);

namespace App\View\Components\FilterBanner;

use Illuminate\Contracts\View\View;

final class Sort extends FilterField
{
    /**
     * @var array<string, string>
     */
    public const DEFAULT_OPTIONS = [
        '' => 'Newest First',
        'oldest' => 'Oldest First',
        'title_asc' => 'Title A\u{2013}Z',
        'title_desc' => 'Title Z\u{2013}A',
    ];

    /**
     * @param  array<string, string>|null  $options
     */
    public function __construct(
        string $label = 'Sort',
        string $name = 'sort',
        public ?array $options = null,
        bool $auth = false,
        int $colspan = 1,
    ) {
        parent::__construct($label, $name, $auth, $colspan);
        $this->options ??= self::DEFAULT_OPTIONS;
    }

    public function render(): View
    {
        return view('components.filter-banner.sort');
    }
}
