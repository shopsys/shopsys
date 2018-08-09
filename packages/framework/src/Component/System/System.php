<?php

namespace Shopsys\FrameworkBundle\Component\System;

class System
{
    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function isMac()
    {
        return stripos(PHP_OS, 'darwin') === 0;
    }
}
