<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Exception;

use Exception;

class InvalidDomainIdException extends Exception implements DomainException
{
    public function __construct(string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
