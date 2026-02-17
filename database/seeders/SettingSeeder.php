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
            'profile_email' => $user?->email,
        ];

        foreach ($defaults as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }
    }
}
