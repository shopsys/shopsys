<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine\Exception;

use Exception;

class UnexpectedTypeException extends Exception implements EntityException
{
    public function __construct(string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
