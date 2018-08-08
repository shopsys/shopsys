<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine\Cache\Exception;

use Exception;

class InvalidArgumentException extends Exception implements DoctrineCacheException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
