<?php

declare(strict_types=1);

use Symfony\Component\VarDumper\VarDumper;

/**
 * @param mixed $var
 */
function d($var)
{
    foreach (func_get_args() as $var) {
        VarDumper::dump($var);
    }
}
