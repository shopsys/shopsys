<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/autoload.php';

file_put_contents('php://output', Environment::getEnvironment(true));
