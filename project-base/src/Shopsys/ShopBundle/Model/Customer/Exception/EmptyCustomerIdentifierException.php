<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Exception;

class EmptyCustomerIdentifierException extends Exception implements CustomerException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
