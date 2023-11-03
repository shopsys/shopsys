<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/autoload.php';

return static function () {
    file_put_contents('php://output', Environment::getEnvironment());
};
