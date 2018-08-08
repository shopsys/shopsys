<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use Exception;

class OrderItemHasNoIdException extends Exception implements OrderItemException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
