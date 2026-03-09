<?php

declare(strict_types=1);

it('adds security headers to all responses', function (): void {
    $response = $this->get('/up');

    $response->assertSuccessful();

    expect($response->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toBe('camera=(), microphone=(), geolocation=(), browsing-topics=()');
});
