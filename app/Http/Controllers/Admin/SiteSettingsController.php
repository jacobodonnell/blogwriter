<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePageSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.site');
    }

    public function update(UpdatePageSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            if (blank($value)) {
                Setting::query()->where('key', $key)->delete();
                Cache::forget('setting.'.$key);
            } else {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings.site')->with('success', 'Site settings updated.');
    }
}
