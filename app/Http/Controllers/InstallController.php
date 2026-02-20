<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\InstallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class InstallController extends Controller
{
    public function index(InstallService $installService): View|RedirectResponse
    {
        if ($installService->isAlreadyInstalled()) {
            return redirect()->route('admin.dashboard');
        }

        return view('install.cli');
    }
}
