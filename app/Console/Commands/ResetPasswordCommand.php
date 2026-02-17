<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PasswordGenerator;
use App\Services\PasswordRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\warning;

class ResetPasswordCommand extends Command
{
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

    protected function promptForPassword(): string
    {
        $suggestedPassphrase = PasswordGenerator::generate();

        info('Suggested secure passphrase (memorable & strong):');
        info($suggestedPassphrase);
        $this->newLine();

        $useSuggested = confirm(
            label: 'Use this passphrase?',
            default: false,
        );

        if ($useSuggested) {
            info('Your passphrase: '.$suggestedPassphrase);
            info('Please save this in a password manager!');
            $this->newLine();

            return $suggestedPassphrase;
        }

        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $newPassword = password(
                label: 'New password',
                required: true,
            );

            $confirmation = password(
                label: 'Confirm password',
                required: true,
            );

            if ($newPassword === $confirmation) {
                return $newPassword;
            }

            warning('Passwords do not match. Please try again.');
            $this->newLine();
            $attempts++;
        }

        warning('Maximum attempts reached. Generating a secure passphrase for you.');
        $generatedPassphrase = PasswordGenerator::generate();
        info('Your generated passphrase: '.$generatedPassphrase);
        info('Please save this in a password manager!');

        return $generatedPassphrase;
    }
}
