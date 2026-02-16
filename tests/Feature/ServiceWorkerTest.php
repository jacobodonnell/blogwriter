<?php

declare(strict_types=1);

it('uses stale-while-revalidate for storage paths', function () {
    $swContent = file_get_contents(public_path('sw.js'));

    expect($swContent)
        ->toContain("url.pathname.startsWith('/storage/')")
        ->toContain('stale-while-revalidate');
});

it('uses cache-first for other static assets', function () {
    $swContent = file_get_contents(public_path('sw.js'));

    expect($swContent)
        ->toContain('cache-first')
        ->toContain('isStaticAsset');
});

it('has an up-to-date cache name', function () {
    $swContent = file_get_contents(public_path('sw.js'));

    expect($swContent)
        ->toContain("CACHE_NAME = 'blogwriter-v2'")
        ->not->toContain("CACHE_NAME = 'blogwriter-v1'");
});
