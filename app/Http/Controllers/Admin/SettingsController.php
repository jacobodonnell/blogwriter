<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings')->with('success', 'Profile updated successfully.');
    }
}
