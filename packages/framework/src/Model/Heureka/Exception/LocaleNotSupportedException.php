<?php

namespace Shopsys\FrameworkBundle\Model\Heureka\Exception;

use Exception;

class LocaleNotSupportedException extends Exception implements HeurekaException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
