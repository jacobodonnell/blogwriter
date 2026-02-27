<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ValidatesInput;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

final class CreateUserCommand extends Command
{
    use ValidatesInput;

    protected $signature = 'blogwriter:create-user
                            {--name= : User\'s display name (prompted if not provided)}
                            {--email= : User\'s email address (prompted if not provided)}
                            {--password= : Password (auto-generated if not provided)}
                            {--force : Replace existing user without confirmation}';

    protected $description = 'Create the BlogWriter admin user (supports non-interactive mode with --name and --email)';

    public function handle(): int
    {
        $name = $this->option('name') ?? text(
            label: "What is the user's display name?",
            placeholder: 'E.g. Jane Doe',
            required: true,
        );

        $email = $this->option('email');

        if ($email !== null) {
            if ($error = $this->validateEmail($email)) {
                $this->error('Invalid email: '.$error);

                return self::FAILURE;
            }
        } else {
            $email = text(
                label: "What is the user's email address?",
                placeholder: 'E.g. you@example.com',
                required: true,
                validate: $this->validateEmail(...),
            );
        }

        $existingUser = User::first();

        if ($existingUser) {
            $this->warn(sprintf('Existing user: %s (%s)', $existingUser->name, $existingUser->email));

            if (! $this->option('force') && ! confirm('Replace this user?')) {
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
