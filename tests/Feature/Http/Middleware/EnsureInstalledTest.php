<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureInstalled;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

function expectAbort(int $status, string $uri): void
{
    try {
        runMiddleware(createMiddlewareRequest($uri));
        test()->fail("Expected {$status} HttpException was not thrown");
    } catch (HttpException $e) {
        expect($e->getStatusCode())->toBe($status);
    }
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

it('redirects public frontend to install when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install');
});

it('redirects articles to install when not installed', function (): void {
    $response = runMiddleware(createMiddlewareRequest('/articles'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('/install');
});

it('passes through when installed with healthy database', function (): void {
    User::factory()->create();
    file_put_contents(storage_path('installed.lock'), now());

    $envPath = base_path('.env');
    $hadEnv = file_exists($envPath);
    if (! $hadEnv) {
        file_put_contents($envPath, 'APP_KEY=base64:'.base64_encode(random_bytes(32)));
    }

    try {
        $response = runMiddleware(createMiddlewareRequest('/admin'));

        expect($response->getContent())->toBe('passed');
    } finally {
        if (! $hadEnv) {
            @unlink($envPath);
        }
    }
});

it('redirects admin to install when lock exists but database is empty', function (): void {
    file_put_contents(storage_path('installed.lock'), now());

    $envPath = base_path('.env');
    $hadEnv = file_exists($envPath);
    if (! $hadEnv) {
        file_put_contents($envPath, 'APP_KEY=base64:'.base64_encode(random_bytes(32)));
    }

    // Point config at a temp empty file to simulate empty/missing database
    $tempDb = tempnam(sys_get_temp_dir(), 'bw_test_');
    file_put_contents($tempDb, '');
    config(['database.connections.sqlite.database' => $tempDb]);

    try {
        $response = runMiddleware(createMiddlewareRequest('/admin'));

        expect($response->getStatusCode())->toBe(302)
            ->and($response->headers->get('Location'))->toContain('/install')
            ->and(file_exists(storage_path('installed.lock')))->toBeFalse();
    } finally {
        if (! $hadEnv) {
            @unlink($envPath);
        }
        @unlink($tempDb);
    }
});

it('redirects to install when .env is missing even with lock file and healthy database', function (): void {
    User::factory()->create();
    file_put_contents(storage_path('installed.lock'), now());

    $envPath = base_path('.env');

    // Ensure .env does not exist (test environment typically has no .env)
    $hadEnv = file_exists($envPath);
    if ($hadEnv) {
        rename($envPath, $envPath.'.test-backup');
    }

    try {
        $response = runMiddleware(createMiddlewareRequest('/admin'));

        expect($response->getStatusCode())->toBe(302)
            ->and($response->headers->get('Location'))->toContain('/install');
    } finally {
        if ($hadEnv) {
            rename($envPath.'.test-backup', $envPath);
        }
    }
});
