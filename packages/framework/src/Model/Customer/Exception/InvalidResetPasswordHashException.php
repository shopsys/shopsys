<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class InvalidResetPasswordHashException extends Exception implements CustomerException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
