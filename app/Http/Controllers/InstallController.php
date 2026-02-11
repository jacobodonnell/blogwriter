<?php

namespace App\Http\Controllers;

class InstallController extends Controller
{
    public function index(): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        // If already installed, redirect to admin
        if (file_exists(storage_path('installed.lock'))) {
            return redirect('/admin');
        }

        return view('install.index');
    }
}
