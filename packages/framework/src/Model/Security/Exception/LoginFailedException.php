<?php

namespace Shopsys\FrameworkBundle\Model\Security\Exception;

use Exception;

class LoginFailedException extends Exception implements SecurityException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
