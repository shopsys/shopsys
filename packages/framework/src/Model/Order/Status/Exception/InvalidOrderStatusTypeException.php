<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;

class InvalidOrderStatusTypeException extends Exception implements OrderStatusException
{
    /**
     * @param string $orderStatusType
     * @param \Exception|null $previous
     */
    public function __construct(string $orderStatusType, ?Exception $previous = null)
    {
        parent::__construct('Order status type "' . $orderStatusType . '" is not valid', 0, $previous);
    }
}
