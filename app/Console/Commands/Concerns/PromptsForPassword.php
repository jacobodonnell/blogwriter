<?php

namespace App\Console\Commands\Concerns;

use App\Services\PasswordGenerator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\warning;

trait PromptsForPassword
{
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
                label: 'Create a password',
                placeholder: 'Min 8 characters',
            );

            $confirmation = password(
                label: 'Confirm your password',
                placeholder: 'Enter the same password',
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
