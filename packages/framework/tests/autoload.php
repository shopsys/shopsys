<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = file_exists(
    __DIR__ . '/../vendor/autoload.php',
) ? require __DIR__ . '/../vendor/autoload.php' : require __DIR__ . '/../../../vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;
