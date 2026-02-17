<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\PromptsForPassword;
use App\Models\User;
use App\Services\PasswordRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ResetPasswordCommand extends Command
{
    use PromptsForPassword;

    protected $signature = 'blogwriter:user:reset-password
                            {--password= : New password (prompted if not provided)}';

    protected $description = 'Reset the user password';

    public function handle(): int
    {
        $user = User::first();

        if (! $user) {
            $this->error('No user found. Run the installer first.');

            return self::FAILURE;
        }

        $newPassword = $this->option('password') ?? $this->promptForPassword();

        $validator = Validator::make(
            ['password' => $newPassword],
            ['password' => ['required', 'string', PasswordRules::rules()]],
            PasswordRules::messages(),
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->update(['password' => $newPassword]);

        $this->info(sprintf('Password reset successfully for %s (%s).', $user->name, $user->email));

        return self::SUCCESS;
    }
}
