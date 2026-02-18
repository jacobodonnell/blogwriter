<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlaceholderImageRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class PlaceholderImageController extends Controller
{
    public function update(UpdatePlaceholderImageRequest $request): RedirectResponse
    {
        $path = $request->file('placeholder_image')->store('blogwriter', 'public');

        // Delete old placeholder if it's different
        $oldPath = Setting::get('site_placeholder_image');
        if ($oldPath && $oldPath !== $path && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('site_placeholder_image', $path);

        return redirect()->route('admin.settings.site')->with('success', 'Placeholder image updated successfully.');
    }
}
