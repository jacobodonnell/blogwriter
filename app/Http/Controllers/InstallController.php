<?php

namespace App\Http\Controllers;

use App\Services\InstallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function index(InstallService $installService): View|RedirectResponse
    {
        if ($installService->isAlreadyInstalled()) {
            return redirect('/admin');
        }

        return view('install.cli');
    }
}
