<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomizerSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CustomizerSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.customizer', [
            'currentMode' => setting('customizer_editor_mode', 'fullscreen'),
        ]);
    }

    public function update(UpdateCustomizerSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.customizer')->with('success', 'Customizer settings saved.');
    }
}
