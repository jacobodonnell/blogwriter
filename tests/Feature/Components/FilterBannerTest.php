<?php

declare(strict_types=1);

use App\View\Components\FilterBanner\CategorySelect;
use App\View\Components\FilterBanner\FilterBanner;
use App\View\Components\FilterBanner\PerPage;
use App\View\Components\FilterBanner\Search;
use App\View\Components\FilterBanner\Select;
use App\View\Components\FilterBanner\Sort;
use Illuminate\Support\Collection;

// --- FilterField shouldRender ---

it('renders filter field when auth is false', function (): void {
    $component = new Search(auth: false);

    expect($component->shouldRender())->toBeTrue();
});

it('hides filter field when auth is true and user is guest', function (): void {
    $component = new Search(auth: true);

    expect($component->shouldRender())->toBeFalse();
});

it('renders filter field when auth is true and user is logged in', function (): void {
    $this->actingAs(App\Models\User::factory()->create());

    $component = new Search(auth: true);

    expect($component->shouldRender())->toBeTrue();
});

// --- FilterField colspanClass ---

it('computes empty colspan class for default colspan', function (): void {
    $component = new Search(colspan: 1);

    expect($component->colspanClass)->toBe('');
});

it('computes correct colspan class for 2', function (): void {
    $component = new Search(colspan: 2);

    expect($component->colspanClass)->toBe('sm:col-span-2');
});

it('computes correct colspan class for 3', function (): void {
    $component = new Search(colspan: 3);

    expect($component->colspanClass)->toBe('sm:col-span-3');
});

it('computes correct colspan class for 4', function (): void {
    $component = new Search(colspan: 4);

    expect($component->colspanClass)->toBe('sm:col-span-4');
});

// --- FilterBanner hasFilters and activeFilterCount ---

it('detects no active filters', function (): void {
    $component = new FilterBanner(
        action: '/test',
        target: 'results',
        clearRoute: '/test',
    );

    expect($component->hasFilters)->toBeFalse()
        ->and($component->activeFilterCount)->toBe(0);
});

it('detects active filters from request', function (): void {
    $this->get('/articles?search=test&status=public');

    $component = new FilterBanner(
        action: '/test',
        target: 'results',
        clearRoute: '/test',
        filterParams: ['search', 'status'],
    );

    expect($component->hasFilters)->toBeTrue()
        ->and($component->activeFilterCount)->toBe(2);
});

it('only counts params in filterParams array', function (): void {
    $this->get('/articles?search=test&unrelated=value');

    $component = new FilterBanner(
        action: '/test',
        target: 'results',
        clearRoute: '/test',
        filterParams: ['search', 'category'],
    );

    expect($component->activeFilterCount)->toBe(1);
});

// --- Sort default options ---

it('uses default sort options when none provided', function (): void {
    $component = new Sort;

    expect($component->options)->toBe(Sort::DEFAULT_OPTIONS);
});

it('uses custom sort options when provided', function (): void {
    $custom = ['foo' => 'Bar'];
    $component = new Sort(options: $custom);

    expect($component->options)->toBe($custom);
});

// --- PerPage defaults ---

it('uses default per page options', function (): void {
    $component = new PerPage;

    expect($component->options)->toBe([10, 20, 50, 100])
        ->and($component->default)->toBe(20);
});

// --- Select props ---

it('passes select props correctly', function (): void {
    $component = new Select(
        name: 'status',
        label: 'Status',
        options: ['public' => 'Public'],
        emptyLabel: 'All Status',
    );

    expect($component->name)->toBe('status')
        ->and($component->label)->toBe('Status')
        ->and($component->options)->toBe(['public' => 'Public'])
        ->and($component->emptyLabel)->toBe('All Status');
});

// --- CategorySelect ---

it('passes categories collection', function (): void {
    $categories = new Collection(['a', 'b']);
    $component = new CategorySelect(categories: $categories);

    expect($component->categories)->toBe($categories)
        ->and($component->label)->toBe('Category');
});

// --- Rendering ---

it('renders filter banner component', function (): void {
    $this->actingAs(App\Models\User::factory()->create());

    $view = $this->blade(
        '<x-filter-banner action="/test" target="results" clearRoute="/test">
            <x-filter-banner.search placeholder="Search..." />
        </x-filter-banner>'
    );

    $view->assertSee('Filters')
        ->assertSee('Search...');
});

it('renders filter banner with active filter badge', function (): void {
    $this->actingAs(App\Models\User::factory()->create());

    $view = $this->get('/articles?search=test');

    // The badge should appear because we have an active search filter
    $view->assertSee('badge-primary');
});

it('hides auth-gated filter field for guests', function (): void {
    $view = $this->blade(
        '<x-filter-banner action="/test" target="results" clearRoute="/test">
            <x-filter-banner.select name="status" label="Status" :options="[\'public\' => \'Public\']" :auth="true" />
        </x-filter-banner>'
    );

    $view->assertDontSee('Status');
});

it('shows auth-gated filter field for logged-in users', function (): void {
    $this->actingAs(App\Models\User::factory()->create());

    $view = $this->blade(
        '<x-filter-banner action="/test" target="results" clearRoute="/test">
            <x-filter-banner.select name="status" label="Status" :options="[\'public\' => \'Public\']" :auth="true" />
        </x-filter-banner>'
    );

    $view->assertSee('Status');
});
