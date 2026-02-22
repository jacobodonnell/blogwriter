<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureInstalled;
use App\Models\User;
use Illuminate\Http\Request;

beforeEach(function (): void {
    @unlink(storage_path('installed.lock'));
});

afterEach(function (): void {
    @unlink(storage_path('installed.lock'));
    app()->detectEnvironment(fn () => 'testing');
});

function createMiddlewareRequest(string $uri, string $method = 'GET'): Request
{
    return Request::create($uri, $method);
}

function runMiddleware(Request $request): mixed
{
    // Force non-testing environment so the middleware actually runs
    app()->detectEnvironment(fn () => 'local');

    $middleware = app(EnsureInstalled::class);

    return $middleware->handle($request, fn ($req) => response('passed'));
}

it('allows install route through when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/install'));

    expect($response->getContent())->toBe('passed');
});

it('allows install sub-routes through when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/install/finalize'));

    expect($response->getContent())->toBe('passed');
});

it('redirects login to install when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/login'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install');
});

it('redirects admin to install when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/admin'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install');
});

it('redirects admin sub-routes to install when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/admin/articles'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install');
});

it('returns 404 for public frontend when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/'));

    expect($response->getStatusCode())->toBe(404);
})->throws(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

it('returns 404 for articles when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/articles'));

    expect($response->getStatusCode())->toBe(404);
})->throws(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

it('passes through when installed with healthy database', function (): void {
    User::factory()->create();
    file_put_contents(storage_path('installed.lock'), now());

    $response = runMiddleware(createMiddlewareRequest('/admin'));

    expect($response->getContent())->toBe('passed');
});

it('redirects admin to install when lock exists but database is empty', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    // Point config at a temp empty file to simulate empty/missing database
    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    file_put_contents($tempDb, '');
    config(['database.connections.sqlite.database' => $tempDb]);

    $response = runMiddleware(createMiddlewareRequest('/admin'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install')
        ->and(file_exists(storage_path('installed.lock')))->toBeFalse();

    @unlink($tempDb);
});
