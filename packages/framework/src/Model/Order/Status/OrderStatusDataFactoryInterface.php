<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

interface OrderStatusDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    public function create(): OrderStatusData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    public function createFromOrderStatus(OrderStatus $orderStatus): OrderStatusData;
}
