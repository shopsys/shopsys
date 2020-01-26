<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/autoload.php';
require dirname(__DIR__) . '/config/bootstrap.php';

file_put_contents('php://output', $_ENV['APP_ENV']);
