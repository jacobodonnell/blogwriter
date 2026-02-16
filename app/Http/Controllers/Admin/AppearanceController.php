<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAppearanceRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppearanceController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.appearance', [
            'lightThemes' => config('appearance.themes_light'),
            'darkThemes' => config('appearance.themes_dark'),
            'fonts' => config('appearance.fonts'),
            'fontCategories' => config('appearance.font_categories'),
            'currentLight' => setting('theme_light', config('appearance.defaults.theme_light')),
            'currentDark' => setting('theme_dark', config('appearance.defaults.theme_dark')),
            'currentFont' => setting('theme_font', config('appearance.defaults.theme_font')),
        ]);
    }

    public function update(UpdateAppearanceRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.appearance')->with('success', 'Appearance settings saved.');
    }
}
