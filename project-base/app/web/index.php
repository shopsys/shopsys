<?php

use App\Environment;
use App\Kernel;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

require_once dirname(__DIR__) . '/app/autoload.php';

$_SERVER['APP_ENV'] = Environment::getEnvironment();
$_SERVER['APP_DEBUG'] = EnvironmentType::isDebug($_SERVER['APP_ENV']);

setlocale(LC_CTYPE, 'en_US.utf8');
setlocale(LC_NUMERIC, 'en_US.utf8');

return static function (array $context) {
    ini_set('display_errors', 0);

    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
