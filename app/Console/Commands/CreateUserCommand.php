<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class CreateUserCommand extends Command
{
    protected $signature = 'blogwriter:user:create
                            {name : User\'s display name}
                            {email : User\'s email address}
                            {--password= : Password (auto-generated if not provided)}
                            {--force : Replace existing user without confirmation}';

    protected $description = 'Create the BlogWriter admin user (replaces existing user if one exists)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');

        $existingUser = User::first();

        if ($existingUser) {
            $this->warn(sprintf('Existing user: %s (%s)', $existingUser->name, $existingUser->email));

            if (! $this->option('force') && ! $this->confirm('Replace this user?')) {
                $this->info('Cancelled.');

                return self::SUCCESS;
            }

            $existingUser->delete();
            $this->info('Existing user removed.');
        }

        $password = $this->option('password');
        $passwordWasGenerated = false;

        if (! $password) {
            $password = Str::random(16);
            $passwordWasGenerated = true;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $this->info('User created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Verified', 'Yes'],
            ]
        );

        if ($passwordWasGenerated) {
            $this->warn('Generated Password: '.$password);
            $this->line('Please save this password - it will not be displayed again.');
        }

        return self::SUCCESS;
    }
}
