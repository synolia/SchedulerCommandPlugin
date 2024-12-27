<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([
        \dirname(__DIR__, 1) . '/src',
        \dirname(__DIR__, 1) . '/tests/PHPUnit',
    ])
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withPhpSets(php82: true)
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        doctrineCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withTypeCoverageLevel(0)
    ->withSets([
        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_61,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_64,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
    ]);
