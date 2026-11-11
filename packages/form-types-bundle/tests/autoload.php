<?php

declare(strict_types=1);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = file_exists(
    __DIR__ . '/../vendor/autoload.php',
) ? require __DIR__ . '/../vendor/autoload.php' : require __DIR__ . '/../../../vendor/autoload.php';

return $loader;
