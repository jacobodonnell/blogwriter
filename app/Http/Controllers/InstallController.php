<?php

namespace App\Http\Controllers;

use App\Http\Requests\Install\InstallAccountRequest;
use App\Services\InstallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(protected InstallService $installService) {}

    public function index(): View|RedirectResponse
    {
        if ($this->installService->isAlreadyInstalled()) {
            return redirect('/admin');
        }

        return view('install.index');
    }

    public function check(): JsonResponse
    {
        if ($this->installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        return response()->json($this->installService->checkRequirements());
    }

    public function account(InstallAccountRequest $request): JsonResponse
    {
        if ($this->installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        $request->session()->put('install_config', [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'site_name' => $request->validated('site_name'),
            'site_url' => $request->validated('site_url'),
            'seed_demo' => $request->boolean('seed_demo'),
        ]);

        return response()->json(['success' => true]);
    }

    public function finalize(Request $request): JsonResponse
    {
        if ($this->installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        $config = $request->session()->get('install_config');

        if (! $config) {
            abort(422, 'No installation configuration found. Please complete the account step first.');
        }

        $this->installService->ensureStorageDirectories();
        $this->installService->createStorageLink();
        $this->installService->setupEnvironmentFile();
        $this->installService->generateAppKey();
        $this->installService->updateEnvironmentFile($config);
        $this->installService->runMigrations();
        $this->installService->createUser($config);
        $this->installService->clearCaches();
        $this->installService->createLockFile();

        $request->session()->forget('install_config');

        if ($config['seed_demo'] ?? false) {
            $request->session()->put('install_seed_allowed', true);
        }

        $siteUrl = $config['site_url'];

        return response()->json([
            'success' => true,
            'siteUrl' => $siteUrl,
            'adminUrl' => rtrim((string) $siteUrl, '/').'/admin',
        ]);
    }

    public function seed(Request $request): JsonResponse
    {
        // Seed is only allowed immediately after finalize, tracked via session flag
        if (! $request->session()->get('install_seed_allowed')) {
            abort(403, 'Seeding is not allowed at this time.');
        }

        $this->installService->seedDemoContent();

        $request->session()->forget('install_seed_allowed');

        return response()->json(['success' => true]);
    }

    public function passphrase(): JsonResponse
    {
        if ($this->installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        return response()->json([
            'passphrase' => $this->installService->generatePassphrase(),
        ]);
    }
}
