<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;

/**
 * @method \App\Model\Order\Item\OrderItem createTransport(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, \App\Model\Transport\Transport $transport)
 * @method \App\Model\Order\Item\OrderItem createPayment(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, \App\Model\Payment\Payment $payment)
 * @method \App\Model\Order\Item\OrderItem createDiscount(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order)
 * @method \App\Model\Order\Item\OrderItem createOrderItem(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order)
 * @method \App\Model\Order\Item\OrderItem createRounding(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order)
 * @method \App\Model\Order\Item\OrderItem createProduct(\App\Model\Order\Item\OrderItemData $orderItemData, \App\Model\Order\Order $order, \App\Model\Product\Product|null $product)
 */
class OrderItemFactory extends BaseOrderItemFactory
{
}
