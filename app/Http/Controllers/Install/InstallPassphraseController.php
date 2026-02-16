<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\InstallService;
use Illuminate\Http\JsonResponse;

class InstallPassphraseController extends Controller
{
    public function store(InstallService $installService): JsonResponse
    {
        if ($installService->isAlreadyInstalled()) {
            abort(403, 'BlogWriter is already installed.');
        }

        return response()->json([
            'passphrase' => $installService->generatePassphrase(),
        ]);
    }
}
