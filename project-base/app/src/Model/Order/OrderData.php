<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @property \App\Model\Order\Item\OrderItemData|null $orderPayment
 * @property \App\Model\Order\Item\OrderItemData|null $orderTransport
 * @method \App\Model\Order\Item\OrderItemData[] getNewItemsWithoutTransportAndPayment()
 * @property \App\Model\Order\Status\OrderStatus|null $status
 * @property \App\Model\Order\Item\OrderItemData[] $items
 * @method \App\Model\Order\Item\OrderItemData[] getItemsByType(string $type)
 * @method addItem(\App\Model\Order\Item\OrderItemData $item)
 * @property \App\Model\Customer\User\CustomerUser $customerUser
 * @method \App\Model\Order\Item\OrderItemData[] getItemsWithoutTransportAndPayment()
 * @method setItemsWithoutTransportAndPayment(\App\Model\Order\Item\OrderItemData[] $items)
 */
class OrderData extends BaseOrderData
{
}
