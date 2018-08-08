<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

class OrderStatusService
{
    public function checkForDelete(OrderStatus $oldOrderStatus): void
    {
        if ($oldOrderStatus->getType() !== OrderStatus::TYPE_IN_PROGRESS) {
            throw new \Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusDeletionForbiddenException($oldOrderStatus);
        }
    }
}
