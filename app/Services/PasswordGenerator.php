<?php

namespace App\Services;

class PasswordGenerator
{
    private static ?array $wordlist = null;

    public static function generate(): string
    {
        if (self::$wordlist === null) {
            self::loadWordlist();
        }

        $words = [];
        for ($i = 0; $i < 4; $i++) {
            $words[] = self::$wordlist[array_rand(self::$wordlist)];
        }

        $number = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);

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
