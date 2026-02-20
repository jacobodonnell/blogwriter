<?php

declare(strict_types=1);

it('does not define down methods in migrations', function () {
    $migrations = glob(database_path('migrations/*.php'));

    expect($migrations)->not->toBeEmpty();

    foreach ($migrations as $file) {
        expect(file_get_contents($file))
            ->not->toContain('function down(', 'Migration '.basename($file).' should not define a down() method');
    }
});
