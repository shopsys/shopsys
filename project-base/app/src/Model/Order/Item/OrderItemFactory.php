<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;

/**
 * @method \App\Model\Order\Item\OrderItem createTransport(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, \App\Model\Transport\Transport $transport)
 * @method \App\Model\Order\Item\OrderItem createPayment(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, \App\Model\Payment\Payment $payment)
 * @method \App\Model\Order\Item\OrderItem createDiscount(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order)
 * @method \App\Model\Order\Item\OrderItem doCreateOrderItem(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, string $orderItemType)
 * @method \App\Model\Order\Item\OrderItem createRounding(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order)
 */
class OrderItemFactory extends BaseOrderItemFactory
{
    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Order\Item\OrderItem
     */
    #[Override]
    public function createProduct(
        BaseOrderItemData $orderItemData,
        BaseOrder $order,
        ?BaseProduct $product,
    ): BaseOrderItem {
        /** @var \App\Model\Order\Item\OrderItem $orderProduct */
        $orderProduct = parent::createProduct($orderItemData, $order, $product);

        $orderProduct->setRelatedOrderItem($orderItemData->relatedOrderItem);

        return $orderProduct;
    }
}
