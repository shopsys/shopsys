<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory as BaseOrderItemDataFactory;

/**
 * @method void addFieldsByOrderItemType(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Item\OrderItem $orderItem)
 * @method bool isUsingPriceCalculation(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Item\OrderItem $orderItem)
 * @method \App\Model\Order\Item\OrderItemData createFromOrderItem(\App\Model\Order\Item\OrderItem $orderItem)
 * @method void fillFromOrderItem(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Item\OrderItem $orderItem)
 * @method \App\Model\Order\Item\OrderItemData create(string $orderItemType)
 * @method \App\Model\Order\Item\OrderItemData createFromQuantifiedProduct(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct, \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPriceInterface $quantifiedItemPrice, string $locale)
 * @method \App\Model\Order\Item\OrderItemData createRounding(\Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $roundingPrice, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 */
class OrderItemDataFactory extends BaseOrderItemDataFactory
{
    /**
     * @return \App\Model\Order\Item\OrderItemData
     */
    #[Override]
    protected function createInstance(): BaseOrderItemData
    {
        return new OrderItemData();
    }
}
