<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory as BaseOrderItemDataFactory;

/**
 * @method \App\Model\Order\Item\OrderItemData create()
 * @method addFieldsByOrderItemType(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Item\OrderItem $orderItem)
 * @method bool isUsingPriceCalculation(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Item\OrderItem $orderItem)
 */
class OrderItemDataFactory extends BaseOrderItemDataFactory
{
    /**
     * @return \App\Model\Order\Item\OrderItemData
     */
    protected function createInstance(): BaseOrderItemData
    {
        return new OrderItemData();
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $orderItem
     * @return \App\Model\Order\Item\OrderItemData
     */
    public function createFromOrderItem(BaseOrderItem $orderItem): BaseOrderItemData
    {
        $orderItemData = new OrderItemData();
        $this->fillFromOrderItem($orderItemData, $orderItem);
        $this->addFieldsByOrderItemType($orderItemData, $orderItem);

        return $orderItemData;
    }

    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Item\OrderItem $orderItem
     */
    protected function fillFromOrderItem(BaseOrderItemData $orderItemData, BaseOrderItem $orderItem): void
    {
        parent::fillFromOrderItem($orderItemData, $orderItem);

        $orderItemData->promoCodeIdentifier = $orderItem->getPromoCodeIdentifier();
    }
}
