<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    protected $signature = 'blogwriter:user:create
                            {name : User\'s display name}
                            {email : User\'s email address}
                            {--password= : Password (auto-generated if not provided)}';

    protected $description = 'Create a new BlogWriter admin user';

    public function handle(): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email '{$email}' already exists.");

            return self::FAILURE;
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
            'password' => Hash::make($password),
        ]);

        $user->email_verified_at = now();
        $user->save();

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
