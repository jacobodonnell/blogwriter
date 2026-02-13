<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed default profile settings from existing user data.
     */
    public function run(): void
    {
        $user = User::first();

        $defaults = [
            'profile_name' => $user?->name ?? config('app.name', 'BlogWriter'),
            'profile_email' => $user?->email,
            'profile_url' => config('app.url'),
        ];

        foreach ($defaults as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }
    }
}
