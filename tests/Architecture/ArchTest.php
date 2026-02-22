<?php

declare(strict_types=1);

arch('enums are string-backed enums')
    ->expect('App\Enums')
    ->toBeStringBackedEnums();

arch('article model uses Status enum')
    ->expect('App\Models\Article')
    ->toUse('App\Enums\Status');

arch('photo model uses Status enum')
    ->expect('App\Models\Photo')
    ->toUse('App\Enums\Status');

arch('app uses strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debugging statements in app code')
    ->expect('App')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);

it('does not define down methods in migrations', function (): void {
    $basePath = dirname(__DIR__, 2);
    $migrations = glob($basePath.'/database/migrations/*.php');

    expect($migrations)->not->toBeEmpty();

    foreach ($migrations as $file) {
        expect(file_get_contents($file))
            ->not->toContain('function down(', 'Migration '.basename($file).' should not define a down() method');
    }
});

it('does not use raw status strings in application code', function (): void {
    $basePath = dirname(__DIR__, 2);

    $directories = [
        $basePath.'/app',
        $basePath.'/database/factories',
        $basePath.'/database/seeders',
    ];

    $pattern = "/['\"]status['\"]\s*=>\s*['\"](?:draft|published)['\"]/";
    $violations = [];

    foreach ($directories as $directory) {
        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (preg_match_all($pattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $line = count(explode("\n", mb_substr($contents, 0, $match[1])));
                    $relativePath = str_replace($basePath.'/', '', $file->getPathname());
                    $violations[] = "{$relativePath}:{$line} — {$match[0]}";
                }
            }
        }
    }

    expect($violations)
        ->toBeEmpty(
            "Raw status strings found. Use Status enum instead:\n".implode("\n", $violations)
        );
});
