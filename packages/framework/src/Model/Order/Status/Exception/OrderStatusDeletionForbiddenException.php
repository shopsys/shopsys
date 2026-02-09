<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Exception;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;

class OrderStatusDeletionForbiddenException extends Exception
{
    public function __construct(protected readonly OrderStatus $orderStatus, ?Exception $previous = null)
    {
        parent::__construct('Deletion of order status ID = ' . $orderStatus->getId() . ' is forbidden', 0, $previous);
    }

    public function getOrderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }
}
