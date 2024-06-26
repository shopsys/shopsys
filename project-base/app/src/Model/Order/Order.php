<?php

declare(strict_types=1);

namespace App\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Order\Item\OrderItem> $items
 * @property \App\Model\Transport\Transport $transport
 * @property \App\Model\Payment\Payment $payment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUser()
 * @method \App\Model\Order\Item\OrderItem[] getItems()
 * @method \App\Model\Order\Item\OrderItem[] getProductItems()
 * @method \App\Model\Administrator\Administrator|null getCreatedAsAdministrator()
 * @method editOrderTransport(\App\Model\Order\OrderData $orderData)
 * @method editOrderPayment(\App\Model\Order\OrderData $orderData)
 * @method setDeliveryAddress(\App\Model\Order\OrderData $orderData)
 * @method addItem(\App\Model\Order\Item\OrderItem $item)
 * @method removeItem(\App\Model\Order\Item\OrderItem $item)
 * @method fillCommonFields(\App\Model\Order\OrderData $orderData)
 * @property \App\Model\Order\Status\OrderStatus|null $status
 * @method setStatus(\App\Model\Order\Status\OrderStatus|null $status)
 * @method \App\Model\Order\Status\OrderStatus|null getStatus()
 * @method \Shopsys\FrameworkBundle\Model\Order\OrderEditResult edit(\App\Model\Order\OrderData $orderData)
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Transport\Transport getTransport()
 * @method \App\Model\Order\Item\OrderItem[] getItemsByType(string $type)
 * @method \App\Model\Order\Item\OrderItem[] getDiscountItems()
 * @method \App\Model\Order\Item\OrderItem[] getRoundingItems()
 * @method \App\Model\Order\Item\OrderItem getTransportItem()
 * @method \App\Model\Order\Item\OrderItem getPaymentItem()
 * @method \App\Model\Order\Item\OrderItem[] getItemsWithoutTransportAndPayment()
 * @method editData(\App\Model\Order\OrderData $orderData)
 * @method setCustomerUser(\App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Order\Item\OrderItem[] getItemsSortedWithRelatedItems()
 * @method __construct(\App\Model\Order\OrderData $orderData, string|null $orderNumber = null, string|null $urlHash = null)
 */
#[Loggable(Loggable::STRATEGY_INCLUDE_ALL)]
class Order extends BaseOrder
{
}
