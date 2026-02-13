<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class ProfileCommand extends Command
{
    protected $signature = 'blogwriter:profile
                            {--name= : Display name}
                            {--bio= : Short bio}
                            {--github= : GitHub profile URL}
                            {--twitter= : Twitter/X profile URL}
                            {--email= : Contact email}
                            {--website= : Website URL}';

    protected $description = 'Configure your public profile (h-card) settings';

    public function handle(): int
    {
        if ($this->hasAnyOption()) {
            return $this->handleNonInteractive();
        }

        return $this->handleInteractive();
    }

    protected function handleNonInteractive(): int
    {
        $user = User::first();
        $settings = [];

        if ($this->option('name') !== null) {
            $name = $this->option('name') ?: $user?->name;
            $settings['profile_name'] = $name;
        }

        if ($this->option('bio') !== null) {
            $bio = $this->option('bio');
            if ($bio !== '') {
                $settings['profile_bio'] = $bio;
            }
        }

        if ($this->option('github') !== null) {
            $github = $this->option('github');
            if ($github !== '') {
                if ($error = $this->validateUrl($github)) {
                    $this->error('Invalid GitHub URL: '.$error);

                    return self::FAILURE;
                }

                $settings['social_github'] = $github;
            }
        }

        if ($this->option('twitter') !== null) {
            $twitter = $this->option('twitter');
            if ($twitter !== '') {
                if ($error = $this->validateUrl($twitter)) {
                    $this->error('Invalid Twitter URL: '.$error);

                    return self::FAILURE;
                }

                $settings['social_twitter'] = $twitter;
            }
        }

        if ($this->option('email') !== null) {
            $email = $this->option('email') ?: $user?->email;
            if ($error = $this->validateEmail($email)) {
                $this->error('Invalid email: '.$error);

                return self::FAILURE;
            }

            $settings['social_email'] = $email;
        }

        if ($this->option('website') !== null) {
            $website = $this->option('website');
            if ($website !== '') {
                if ($error = $this->validateUrl($website)) {
                    $this->error('Invalid website URL: '.$error);

                    return self::FAILURE;
                }

                $settings['social_website'] = $website;
            }
        }

        return $this->saveAndDisplay($settings);
    }

    protected function handleInteractive(): int
    {
        $user = User::first();

        info('Configure Your Public Profile');
        note('These settings power your h-card (IndieWeb identity) on your site.');
        $this->newLine();

        $name = text(
            label: 'Display name',
            placeholder: 'Your public name',
            default: Setting::get('profile_name', $user?->name ?? ''),
            required: true,
        );

        $bio = textarea(
            label: 'Bio',
            placeholder: 'A short description about yourself',
            default: Setting::get('profile_bio', ''),
        );

        $github = text(
            label: 'GitHub URL',
            placeholder: 'https://github.com/username',
            default: Setting::get('social_github', ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $twitter = text(
            label: 'Twitter/X URL',
            placeholder: 'https://twitter.com/username',
            default: Setting::get('social_twitter', ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $email = text(
            label: 'Contact email',
            placeholder: 'you@example.com',
            default: Setting::get('social_email', $user?->email ?? ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateEmail($value) : null,
        );

        $website = text(
            label: 'Website URL',
            placeholder: 'https://example.com',
            default: Setting::get('social_website', config('app.url')),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $settings = ['profile_name' => $name];

        if ($bio !== '') {
            $settings['profile_bio'] = $bio;
        }

        if ($github !== '') {
            $settings['social_github'] = $github;
        }

        if ($twitter !== '') {
            $settings['social_twitter'] = $twitter;
        }

        if ($email !== '') {
            $settings['social_email'] = $email;
        }

        if ($website !== '') {
            $settings['social_website'] = $website;
        }

        return $this->saveAndDisplay($settings);
    }

    protected function saveAndDisplay(array $settings): int
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        $this->newLine();
        info('Profile settings saved!');
        $this->newLine();

        $rows = [];
        foreach ($settings as $key => $value) {
            $rows[] = [$key, $value];
        }

        $this->table(['Setting', 'Value'], $rows);

        return self::SUCCESS;
    }

    protected function validateUrl(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) ? null : 'Please enter a valid URL.';
    }

    protected function validateEmail(string $value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : 'Please enter a valid email address.';
    }

    protected function hasAnyOption(): bool
    {
        if ($this->option('name') !== null) {
            return true;
        }
        if ($this->option('bio') !== null) {
            return true;
        }
        if ($this->option('github') !== null) {
            return true;
        }
        if ($this->option('twitter') !== null) {
            return true;
        }
        if ($this->option('email') !== null) {
            return true;
        }

        return $this->option('website') !== null;
    }
}
