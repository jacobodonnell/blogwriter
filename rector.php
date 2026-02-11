<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
        __DIR__.'/routes',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        LaravelSetList::LARAVEL_120,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
    ])
    ->withSkip([
        // Skip rules that may conflict with project conventions
        \Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector::class,
    ]);
