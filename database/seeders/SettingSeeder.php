<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    /**
     * Seed default profile settings from existing user data.
     */
    public function run(): void
    {
        $user = User::first();

        if ($user?->email) {
            Setting::set('profile_email', $user->email);
        }
    }
}
