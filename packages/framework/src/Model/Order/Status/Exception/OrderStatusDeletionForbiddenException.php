<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class OrderStatusDeletionForbiddenException extends Exception implements OrderStatusException
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @param \Exception|null $previous
     */
    public function __construct(protected readonly OrderStatus $orderStatus, ?Exception $previous = null)
    {
        parent::__construct('Deletion of order status ID = ' . $orderStatus->getId() . ' is forbidden', 0, $previous);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }
}
