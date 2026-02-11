<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean up lock file before each test
    if (file_exists(storage_path('installed.lock'))) {
        unlink(storage_path('installed.lock'));
    }
});

afterEach(function () {
    // Clean up lock file after each test
    if (file_exists(storage_path('installed.lock'))) {
        unlink(storage_path('installed.lock'));
    }
});

it('redirects to install page when not installed', function () {
    $response = $this->get('/');
    
    $response->assertRedirect('/install');
});

it('allows access when installed', function () {
    // Create lock file
    file_put_contents(storage_path('installed.lock'), now());
    
    $response = $this->get('/');
    
    $response->assertSuccessful();
});

it('allows access to install route when not installed', function () {
    $response = $this->get('/install');
    
    $response->assertSuccessful();
});

it('allows access to login route when not installed', function () {
    $response = $this->get('/login');
    
    $response->assertSuccessful();
});
