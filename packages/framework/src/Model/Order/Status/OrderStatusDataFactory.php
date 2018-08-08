<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

class OrderStatusDataFactory implements OrderStatusDataFactoryInterface
{
    public function create(): OrderStatusData
    {
        return new OrderStatusData();
    }

    public function createFromOrderStatus(OrderStatus $orderStatus): OrderStatusData
    {
        $orderStatusData = new OrderStatusData();
        $this->fillFromOrderStatus($orderStatusData, $orderStatus);

        return $orderStatusData;
    }

    protected function fillFromOrderStatus(OrderStatusData $orderStatusData, OrderStatus $orderStatus): void
    {
        $translations = $orderStatus->getTranslations();
        $names = [];
        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $orderStatusData->name = $names;
    }
}
