<?php

namespace Shopsys\FrameworkBundle\Component\HttpFoundation\Exception;

use Exception;

class TooManyRedirectResponsesException extends Exception implements HttpFoundationException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
