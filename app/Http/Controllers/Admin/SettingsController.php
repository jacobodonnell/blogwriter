<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        foreach ($validated as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings')->with('profile_success', 'Profile updated successfully.');
    }

    public function updatePlaceholderImage(Request $request): RedirectResponse
    {
        $request->validate([
            'placeholder_image' => 'required|image|max:2048',
        ]);

        $path = $request->file('placeholder_image')->store('blogwriter', 'public');

        // Delete old placeholder if it's different
        $oldPath = Setting::get('site_placeholder_image');
        if ($oldPath && $oldPath !== $path && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('site_placeholder_image', $path);

        return redirect()->route('admin.settings')->with('placeholder_success', 'Placeholder image updated successfully.');
    }
}
