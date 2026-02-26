<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRobotsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class RobotsSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.robots', [
            'robotsTxt' => setting('robots_txt', self::defaultContent()),
        ]);
    }

    public function update(UpdateRobotsRequest $request): RedirectResponse
    {
        Setting::set('robots_txt', $request->validated('robots_txt'));

        return redirect()->route('admin.settings.robots')->with('success', 'Robots.txt updated.');
    }

    private static function defaultContent(): string
    {
        return "User-agent: *\nDisallow:\n";
    }
}
