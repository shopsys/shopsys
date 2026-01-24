<?php

declare(strict_types=1);

use Symfony\Component\VarDumper\VarDumper;

function d(mixed $var): void
{
    foreach (func_get_args() as $var) {
        VarDumper::dump($var);
    }
}
