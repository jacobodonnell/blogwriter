<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Http\Requests\Install\InstallAccountRequest;
use App\Services\InstallService;
use Illuminate\Http\JsonResponse;

class InstallAccountController extends Controller
{
    public function store(InstallAccountRequest $request, InstallService $installService): JsonResponse
    {
        if ($installService->isAlreadyInstalled()) {
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
}
