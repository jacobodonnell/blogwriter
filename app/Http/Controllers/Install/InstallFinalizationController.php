<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\InstallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallFinalizationController extends Controller
{
    public function store(Request $request, InstallService $installService): JsonResponse
    {
        if ($installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        $config = $request->session()->get('install_config');

        if (! $config) {
            abort(422, 'No installation configuration found. Please complete the account step first.');
        }

        $installService->ensureStorageDirectories();
        $installService->createStorageLink();
        $installService->setupEnvironmentFile();
        $installService->generateAppKey();
        $installService->updateEnvironmentFile($config);
        $installService->runMigrations();
        $installService->createUser($config);
        $installService->clearCaches();
        $installService->createLockFile();

        $request->session()->forget('install_config');

        if ($config['seed_demo'] ?? false) {
            file_put_contents(storage_path('install_seed_allowed'), now());
        }

        $siteUrl = $config['site_url'];

        return response()->json([
            'success' => true,
            'siteUrl' => $siteUrl,
            'adminUrl' => rtrim((string) $siteUrl, '/').'/admin',
        ]);
    }
}
