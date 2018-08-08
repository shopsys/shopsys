<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security\Exception;

use Exception;

class InvalidTokenException extends Exception implements SecurityException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
