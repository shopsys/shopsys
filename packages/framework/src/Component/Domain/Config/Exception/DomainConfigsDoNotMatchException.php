<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config\Exception;

use Exception;

class DomainConfigsDoNotMatchException extends Exception implements DomainConfigException
{
    public function __construct(string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
