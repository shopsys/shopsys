<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security\Exception;

use Exception;

class InvalidTokenException extends Exception implements SecurityException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
