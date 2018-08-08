<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class InvalidOrderingModeException extends Exception implements ProductException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
