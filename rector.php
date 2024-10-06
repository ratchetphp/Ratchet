<?php

declare(strict_types=1);

use Rector\Config\Level\TypeDeclarationLevel;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(true)
    ->withTypeCoverageLevel(9);
