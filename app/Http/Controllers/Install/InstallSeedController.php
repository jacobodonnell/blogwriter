<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Services\InstallService;
use Illuminate\Http\JsonResponse;

class InstallSeedController extends Controller
{
    public function store(InstallService $installService): JsonResponse
    {
        // Seed is only allowed immediately after finalize, tracked via file flag
        // (session is unreliable here because finalize changes the APP_KEY)
        $flagPath = storage_path('install_seed_allowed');
        if (! file_exists($flagPath)) {
            abort(403, 'Seeding is not allowed at this time.');
        }

        $installService->seedDemoContent();

        @unlink($flagPath);

        return response()->json(['success' => true]);
    }
}
