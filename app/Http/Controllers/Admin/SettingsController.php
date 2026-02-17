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
        $validated = $request->validated();

        // Write profile_name to users table directly
        $name = $validated['profile_name'];
        unset($validated['profile_name']);
        $request->user()->update(['name' => $name]);

        // Clean up any leftover profile_name in settings table
        Setting::query()->where('key', 'profile_name')->delete();

        foreach ($validated as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings')->with('profile_success', 'Profile updated successfully.');
    }
}
