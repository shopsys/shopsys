<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis\Exception;

use Exception;

class RedisMultiModeNotSupportedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Using Redis in multi mode is not supported.');
    }
}
