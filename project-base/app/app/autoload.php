<?php

declare(strict_types=1);

$symfonyDumpFunctionPath = 'vendor/symfony/var-dumper/Resources/functions/dump.php';

$projectRootDirectory = __DIR__ . '/..';

// change autoloading source for monorepo
if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
    $projectRootDirectory = __DIR__ . '/../../..';
}

if (file_exists(__DIR__ . $projectRootDirectory . '/' . $symfonyDumpFunctionPath)) {
    require_once __DIR__ . $projectRootDirectory . '/' . $symfonyDumpFunctionPath;
}

$loader = require $projectRootDirectory . '/vendor/autoload.php';

return $loader;
