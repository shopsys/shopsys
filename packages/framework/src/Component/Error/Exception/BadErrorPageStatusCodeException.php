<?php

namespace Shopsys\FrameworkBundle\Component\Error\Exception;

use Exception;

class BadErrorPageStatusCodeException extends Exception implements ErrorException
{
    public function __construct(string $url, int $expectedStatusCode, int $actualStatusCode, Exception $previous = null)
    {
        $message = sprintf(
            'Error page "%s" has "%s" status code, expects "%s".',
            $url,
            $actualStatusCode,
            $expectedStatusCode
        );

        parent::__construct($message, 0, $previous);
    }
}
