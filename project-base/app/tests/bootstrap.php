<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

require dirname(__DIR__) . '/app/autoload.php';

// workaround for https://github.com/symfony/symfony/issues/53812
ErrorHandler::register(null, false);

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
