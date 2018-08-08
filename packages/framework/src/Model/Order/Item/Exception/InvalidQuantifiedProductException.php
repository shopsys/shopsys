<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use Exception;

class InvalidQuantifiedProductException extends Exception implements OrderItemException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
