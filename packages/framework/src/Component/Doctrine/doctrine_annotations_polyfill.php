<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\DocParser;

if (!class_exists(DocParser::class)) {
    require __DIR__ . '/DocParserPolyfill.php';
}
