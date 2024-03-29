<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;

class InvalidOrderStatusTypeException extends Exception implements OrderStatusException
{
    protected int $orderStatusType;

    /**
     * @param int $orderStatusType
     * @param \Exception|null $previous
     */
    public function __construct($orderStatusType, ?Exception $previous = null)
    {
        $this->orderStatusType = $orderStatusType;

        parent::__construct('Order status type ' . $orderStatusType . ' is not valid', 0, $previous);
    }

    /**
     * @return int
     */
    public function getOrderStatusType()
    {
        return $this->orderStatusType;
    }
}
