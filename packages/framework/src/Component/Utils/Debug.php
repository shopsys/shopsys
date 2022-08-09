<?php

namespace Shopsys\FrameworkBundle\Component\Utils;

use Doctrine\Common\Util\Debug as DoctrineDebug;

class Debug
{
    /**
     * @param mixed $var
     * @return string
     */
    public static function export(mixed $var): string
    {
        return DoctrineDebug::dump($var, 2, true, false);
    }
}
