<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Utils;

use Doctrine\Common\Util\Debug as DoctrineDebug;

class Debug
{
    public static function export(mixed $var): string
    {
        return DoctrineDebug::dump($var, 2, true, false);
    }
}
