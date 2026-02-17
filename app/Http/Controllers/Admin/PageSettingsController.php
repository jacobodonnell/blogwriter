<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePageSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;

class PageSettingsController extends Controller
{
    public function update(UpdatePageSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings')->with('subtitles_success', 'Page subtitles updated.');
    }
}
