<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/app/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
