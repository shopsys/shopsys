<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

class OrderItemDataFactory implements OrderItemDataFactoryInterface
{
    public function create(): OrderItemData
    {
        return new OrderItemData();
    }

    public function createFromOrderItem(OrderItem $orderItem): OrderItemData
    {
        $orderItemData = new OrderItemData();
        $this->fillFromOrderItem($orderItemData, $orderItem);

        return $orderItemData;
    }

    protected function fillFromOrderItem(OrderItemData $orderItemData, OrderItem $orderItem)
    {
        $orderItemData->name = $orderItem->getName();
        $orderItemData->priceWithVat = $orderItem->getPriceWithVat();
        $orderItemData->priceWithoutVat = $orderItem->getPriceWithoutVat();
        $orderItemData->vatPercent = $orderItem->getVatPercent();
        $orderItemData->quantity = $orderItem->getQuantity();
        $orderItemData->unitName = $orderItem->getUnitName();
        $orderItemData->catnum = $orderItem->getCatnum();
    }
}
