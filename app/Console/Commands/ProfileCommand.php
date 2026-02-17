<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ValidatesInput;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class ProfileCommand extends Command
{
    use ValidatesInput;

    protected $signature = 'blogwriter:profile
                            {--name= : Display name}
                            {--bio= : Short bio}
                            {--github= : GitHub profile URL}
                            {--mastodon= : Mastodon profile URL}
                            {--bluesky= : Bluesky profile URL}
                            {--email= : Contact email}';

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
            $user?->update(['name' => $name]);
            $settings['name'] = $name;
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

                $settings['profile_github'] = $github;
            }
        }

        if ($this->option('mastodon') !== null) {
            $mastodon = $this->option('mastodon');
            if ($mastodon !== '') {
                if ($error = $this->validateUrl($mastodon)) {
                    $this->error('Invalid Mastodon URL: '.$error);

                    return self::FAILURE;
                }

                $settings['profile_mastodon'] = $mastodon;
            }
        }

        if ($this->option('bluesky') !== null) {
            $bluesky = $this->option('bluesky');
            if ($bluesky !== '') {
                if ($error = $this->validateUrl($bluesky)) {
                    $this->error('Invalid Bluesky URL: '.$error);

                    return self::FAILURE;
                }

                $settings['profile_bluesky'] = $bluesky;
            }
        }

        if ($this->option('email') !== null) {
            $email = $this->option('email') ?: $user?->email;
            if ($error = $this->validateEmail($email)) {
                $this->error('Invalid email: '.$error);

                return self::FAILURE;
            }

            $settings['profile_email'] = $email;
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
            default: $user?->name ?? '',
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
            default: Setting::get('profile_github', ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $mastodon = text(
            label: 'Mastodon URL',
            placeholder: 'https://mastodon.social/@username',
            default: Setting::get('profile_mastodon', ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $bluesky = text(
            label: 'Bluesky URL',
            placeholder: 'https://bsky.app/profile/username',
            default: Setting::get('profile_bluesky', ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateUrl($value) : null,
        );

        $email = text(
            label: 'Contact email',
            placeholder: 'you@example.com',
            default: Setting::get('profile_email', $user?->email ?? ''),
            validate: fn (string $value): ?string => $value !== '' ? $this->validateEmail($value) : null,
        );

        $user?->update(['name' => $name]);
        $settings = ['name' => $name];

        if ($bio !== '') {
            $settings['profile_bio'] = $bio;
        }

        if ($github !== '') {
            $settings['profile_github'] = $github;
        }

        if ($mastodon !== '') {
            $settings['profile_mastodon'] = $mastodon;
        }

        if ($bluesky !== '') {
            $settings['profile_bluesky'] = $bluesky;
        }

        if ($email !== '') {
            $settings['profile_email'] = $email;
        }

        return $this->saveAndDisplay($settings);
    }

    protected function saveAndDisplay(array $settings): int
    {
        foreach ($settings as $key => $value) {
            if (str_starts_with((string) $key, 'profile_')) {
                Setting::set($key, $value);
            }
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

    protected function hasAnyOption(): bool
    {
        return collect(['name', 'bio', 'github', 'mastodon', 'bluesky', 'email'])
            ->contains(fn (string $option): bool => $this->option($option) !== null);
    }
}
