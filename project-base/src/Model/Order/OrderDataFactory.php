<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @return \App\Model\Order\OrderData
     */
    public function create(): BaseOrderData
    {
        return new OrderData();
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Order\OrderData
     */
    public function createFromOrder(BaseOrder $order): BaseOrderData
    {
        $orderData = new OrderData();
        $this->fillFromOrder($orderData, $order);

        return $orderData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Order $order
     */
    protected function fillFromOrder(BaseOrderData $orderData, BaseOrder $order)
    {
        parent::fillFromOrder($orderData, $order);
    }
}
