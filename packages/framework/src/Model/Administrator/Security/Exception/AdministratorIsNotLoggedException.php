<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Security\Exception;

use Exception;

class AdministratorIsNotLoggedException extends Exception implements SecurityException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
