<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use Exception;

class OrderItemHasNoIdException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
