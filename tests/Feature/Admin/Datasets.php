<?php

declare(strict_types=1);

/** @var array{fonts: array<string, string>, themes_light: list<string>, themes_dark: list<string>} $appearance */
$appearance = require __DIR__.'/../../../config/appearance.php';

dataset('appearance fonts', array_keys($appearance['fonts']));

dataset('appearance light themes', $appearance['themes_light']);

dataset('appearance dark themes', $appearance['themes_dark']);
