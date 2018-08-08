<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class OrderStatusDeletionForbiddenException extends Exception implements OrderStatusException
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    private $orderStatus;

    public function __construct(OrderStatus $orderStatus, Exception $previous = null)
    {
        $this->orderStatus = $orderStatus;
        parent::__construct('Deletion of order status ID = ' . $orderStatus->getId() . ' is forbidden', 0, $previous);
    }

    public function getOrderStatus(): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        return $this->orderStatus;
    }
}
