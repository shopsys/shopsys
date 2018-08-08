<?php

namespace Shopsys\FrameworkBundle\Component\System;

class System
{
    public function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    public function isMac(): bool
    {
        return stripos(PHP_OS, 'darwin') === 0;
    }
}
