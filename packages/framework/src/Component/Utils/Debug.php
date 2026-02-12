<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Debug
{
    public static function export(mixed $var): string
    {
        $cloner = new VarCloner();
        $cloner->setMaxItems(50);
        $dumper = new CliDumper();

        return $dumper->dump($cloner->cloneVar($var), true);
    }
}
