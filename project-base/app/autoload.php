<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = file_exists(__DIR__ . '/../vendor/autoload.php') ? require __DIR__ . '/../vendor/autoload.php' : require __DIR__ . '/../../vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;
