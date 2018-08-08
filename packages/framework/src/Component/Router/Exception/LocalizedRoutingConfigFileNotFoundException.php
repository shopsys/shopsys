<?php

namespace Shopsys\FrameworkBundle\Component\Router\Exception;

use Exception;

class LocalizedRoutingConfigFileNotFoundException extends Exception implements RouterException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
