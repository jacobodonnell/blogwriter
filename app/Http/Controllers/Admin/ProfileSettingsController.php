<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.profile');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Write profile_name to users table directly
        $name = $validated['profile_name'];
        unset($validated['profile_name']);
        $request->user()->update(['name' => $name]);

        foreach ($validated as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings.profile')->with('success', 'Profile updated successfully.');
    }
}
