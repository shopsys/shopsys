<?php

use Symfony\Component\VarDumper\VarDumper;

/**
 * @param mixed $var
 */
function d($var): void
{
    foreach (func_get_args() as $var) {
        VarDumper::dump($var);
    }
}
