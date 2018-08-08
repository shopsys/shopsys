<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Exception;

use Exception;

class InvalidCartItemException extends Exception implements CartException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
