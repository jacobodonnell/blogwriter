<?php

use App\Models\User;

it('adds no-store cache header to Alpine AJAX responses', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/admin/categories', [
            'X-Alpine-Target' => 'categories-table',
        ]);

    $response->assertSuccessful();
    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('no-store');
});

it('does not add no-store cache header to regular responses', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get('/admin/categories');

    $response->assertSuccessful();

    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->not->toContain('no-store');
});
