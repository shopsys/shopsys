<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class InvalidResetPasswordHashUserException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct($message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
