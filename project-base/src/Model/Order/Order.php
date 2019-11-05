<?php

declare(strict_types=1);

namespace App\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderEditResult;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @property \App\Model\Customer\User|null $customer
 * @property \App\Model\Order\Item\OrderItem[]|\Doctrine\Common\Collections\Collection $items
 * @property \App\Model\Transport\Transport $transport
 * @property \App\Model\Payment\Payment $payment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Order\Item\OrderItem getOrderPayment()
 * @method \App\Model\Transport\Transport getTransport()
 * @method \App\Model\Order\Item\OrderItem getOrderTransport()
 * @method \App\Model\Customer\User|null getCustomer()
 * @method \App\Model\Order\Item\OrderItem[] getItems()
 * @method \App\Model\Order\Item\OrderItem[] getItemsWithoutTransportAndPayment()
 * @method \App\Model\Order\Item\OrderItem[] getTransportAndPaymentItems()
 * @method \App\Model\Order\Item\OrderItem getItemById(int $orderItemId)
 * @method \App\Model\Order\Item\OrderItem[] getProductItems()
 * @method \App\Model\Administrator\Administrator|null getCreatedAsAdministrator()
 * @method editData(\App\Model\Order\OrderData $orderData)
 * @method editOrderTransport(\App\Model\Order\OrderData $orderData)
 * @method editOrderPayment(\App\Model\Order\OrderData $orderData)
 * @method setDeliveryAddress(\App\Model\Order\OrderData $orderData)
 * @method addItem(\App\Model\Order\Item\OrderItem $item)
 * @method removeItem(\App\Model\Order\Item\OrderItem $item)
 */
class Order extends BaseOrder
{
    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \App\Model\Customer\User|null $user
     */
    public function __construct(
        BaseOrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?User $user = null
    ) {
        parent::__construct($orderData, $orderNumber, $urlHash, $user);
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderEditResult
     */
    public function edit(BaseOrderData $orderData): OrderEditResult
    {
        return parent::edit($orderData);
    }
}
