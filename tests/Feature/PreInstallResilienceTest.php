<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\InstallService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

it('returns default when database is unreachable', function (): void {
    config(['database.connections.sqlite.database' => '/tmp/nonexistent_blogwriter_test.sqlite']);

    Cache::forget('setting.theme_light');

    $result = Setting::get('theme_light', 'lofi');

    expect($result)->toBe('lofi');
});

it('provides fallback author name when database is unreachable', function (): void {
    config(['database.connections.sqlite.database' => '/tmp/nonexistent_blogwriter_test.sqlite']);

    Cache::forget('author_name');

    $view = $this->view('components.layouts.partials.public-footer');

    $view->assertSee('Author');
});

it('generates ephemeral APP_KEY when missing', function (): void {
    config(['app.key' => null]);

    $provider = new App\Providers\AppServiceProvider(app());
    $provider->register();

    $key = config('app.key');

    $encoded = Illuminate\Support\Str::after($key, 'base64:');

    expect($key)->toStartWith('base64:')
        ->and($encoded)->toMatch('/^[A-Za-z0-9+\/=]{44}$/');
});

it('redirects to /install for QueryException when app is not installed', function (): void {
    $lockFile = storage_path('installed.lock');
    $hadLockFile = file_exists($lockFile);

    if ($hadLockFile) {
        unlink($lockFile);
    }

    try {
        $handler = app(Illuminate\Contracts\Debug\ExceptionHandler::class);

        $request = Illuminate\Http\Request::create('/');
        $exception = new QueryException('sqlite', 'SELECT 1', [], new PDOException('no such table'));

        $response = $handler->render($request, $exception);

        expect($response->getStatusCode())->toBe(302)
            ->and($response->headers->get('Location'))->toContain('/install');
    } finally {
        if ($hadLockFile) {
            file_put_contents($lockFile, now());
        }
    }
});

it('treats missing .env as not installed even with lock file and healthy database', function (): void {
    App\Models\User::factory()->create();
    file_put_contents(storage_path('installed.lock'), now());

    $envPath = base_path('.env');

    // Ensure .env does not exist (test environment typically has no .env)
    $hadEnv = file_exists($envPath);
    if ($hadEnv) {
        rename($envPath, $envPath.'.test-backup');
    }

    try {
        $service = app(InstallService::class);
        expect($service->isAlreadyInstalled())->toBeFalse();
    } finally {
        if ($hadEnv) {
            rename($envPath.'.test-backup', $envPath);
        }
        @unlink(storage_path('installed.lock'));
    }
});

it('does not catch QueryException when app is installed', function (): void {
    $lockFile = storage_path('installed.lock');
    $hadLockFile = file_exists($lockFile);

    if (! $hadLockFile) {
        file_put_contents($lockFile, now());
    }

    try {
        $handler = app(Illuminate\Contracts\Debug\ExceptionHandler::class);

        $request = Illuminate\Http\Request::create('/');
        $exception = new QueryException('sqlite', 'SELECT 1', [], new PDOException('no such table'));

        $response = $handler->render($request, $exception);

        expect($response->getStatusCode())->toBe(500);
    } finally {
        if (! $hadLockFile) {
            unlink($lockFile);
        }
    }
});
