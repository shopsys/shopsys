<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderEditResult;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Customer\User|null $customer
 * @property \Shopsys\ShopBundle\Model\Order\Item\OrderItem[]|\Doctrine\Common\Collections\Collection $items
 * @property \Shopsys\ShopBundle\Model\Transport\Transport $transport
 * @property \Shopsys\ShopBundle\Model\Payment\Payment $payment
 * @property \Shopsys\ShopBundle\Model\Administrator\Administrator|null $createdAsAdministrator
 * @method \Shopsys\ShopBundle\Model\Payment\Payment getPayment()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem getOrderPayment()
 * @method \Shopsys\ShopBundle\Model\Transport\Transport getTransport()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem getOrderTransport()
 * @method \Shopsys\ShopBundle\Model\Customer\User|null getCustomer()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem[] getItems()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem[] getItemsWithoutTransportAndPayment()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem[] getTransportAndPaymentItems()
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem getItemById(int $orderItemId)
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItem[] getProductItems()
 * @method \Shopsys\ShopBundle\Model\Administrator\Administrator|null getCreatedAsAdministrator()
 * @method editData(\Shopsys\ShopBundle\Model\Order\OrderData $orderData)
 * @method editOrderTransport(\Shopsys\ShopBundle\Model\Order\OrderData $orderData)
 * @method editOrderPayment(\Shopsys\ShopBundle\Model\Order\OrderData $orderData)
 * @method setDeliveryAddress(\Shopsys\ShopBundle\Model\Order\OrderData $orderData)
 * @method addItem(\Shopsys\ShopBundle\Model\Order\Item\OrderItem $item)
 * @method removeItem(\Shopsys\ShopBundle\Model\Order\Item\OrderItem $item)
 */
class Order extends BaseOrder
{
    /**
     * @param \Shopsys\ShopBundle\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \Shopsys\ShopBundle\Model\Customer\User|null $user
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
     * @param \Shopsys\ShopBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderEditResult
     */
    public function edit(BaseOrderData $orderData): OrderEditResult
    {
        return parent::edit($orderData);
    }
}
