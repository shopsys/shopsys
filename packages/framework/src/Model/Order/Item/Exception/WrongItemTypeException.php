<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item\Exception;

use Exception;

class WrongItemTypeException extends Exception
{
    public function __construct(string $expectedType, string $actualType, ?Exception $previous = null)
    {
        $message = sprintf('OrderItem has to be of a type "%s", but it is "%s".', $expectedType, $actualType);

        parent::__construct($message, 0, $previous);
    }
}
