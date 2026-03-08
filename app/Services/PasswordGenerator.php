<?php

declare(strict_types=1);

namespace App\Services;

final class PasswordGenerator
{
    private static ?array $wordlist = null;

    public static function generate(): string
    {
        if (self::$wordlist === null) {
            self::loadWordlist();
        }

        $words = [];
        for ($i = 0; $i < 6; $i++) {
            $words[] = self::$wordlist[array_rand(self::$wordlist)];
        }

        $capitalizeIndex = random_int(0, 5);
        $words[$capitalizeIndex] = ucfirst((string) $words[$capitalizeIndex]);

        $number = mb_str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        return implode('-', $words).'-'.$number;
    }

    private static function loadWordlist(): void
    {
        $path = database_path('data/eff-wordlist.txt');
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        self::$wordlist = [];
        foreach ($lines as $line) {
            $parts = explode("\t", $line);
            if (count($parts) >= 2) {
                self::$wordlist[] = $parts[1]; // Get the word part
            }
        }
    }
}
