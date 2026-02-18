<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('renders admin articles index with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.articles.index'))
        ->assertOk()
        ->assertSee('<title>Articles - '.config('app.name', 'BlogWriter').'</title>', false);
});

it('renders admin dashboard with correct title tag', function (): void {
    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard - '.config('app.name', 'BlogWriter').'</title>', false);
});
