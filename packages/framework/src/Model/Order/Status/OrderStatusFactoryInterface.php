<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

interface OrderStatusFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $data
     * @param int $type
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public function create(OrderStatusData $data, int $type): OrderStatus;
}
