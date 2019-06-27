<?php

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = file_exists(__DIR__ . '/../vendor/autoload.php') ? require __DIR__ . '/../vendor/autoload.php' : require __DIR__ . '/../../vendor/autoload.php';
/* @var $loader \Composer\Autoload\ClassLoader */

AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('returnv');

return $loader;
