<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Order\Item\OrderItem;
use App\Model\Product\Type\ProductType;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderEditResult;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \App\Model\Order\Item\OrderItem[]|\Doctrine\Common\Collections\Collection $items
 * @property \App\Model\Transport\Transport $transport
 * @property \App\Model\Payment\Payment $payment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @method \App\Model\Payment\Payment getPayment()
 * @method \App\Model\Order\Item\OrderItem getOrderPayment()
 * @method \App\Model\Order\Item\OrderItem getOrderTransport()
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUser()
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
     * REMOVED PROPERTY!
     * This property is removed from model, because Order has more Transports.
     *
     * @var null
     * @deprecated
     * @see \App\Component\Doctrine\RemoveMappingsSubscriber
     */
    protected $transport;

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param string $orderNumber
     * @param string $urlHash
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct(
        BaseOrderData $orderData,
        string $orderNumber,
        string $urlHash,
        ?CustomerUser $customerUser = null
    ) {
        parent::__construct($orderData, $orderNumber, $urlHash, $customerUser);
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderEditResult
     */
    public function edit(BaseOrderData $orderData): OrderEditResult
    {
        return parent::edit($orderData);
    }

    /**
     * @return \App\Model\Product\Type\ProductType[]
     */
    public function getAllUsedProductTypes(): array
    {
        $productTypes = [];
        foreach ($this->items as $item) {
            $productType = $item->getProductType();
            $productTypes[$productType->getId()] = $productType;
        }

        return $productTypes;
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @return \App\Model\Order\Item\OrderItem[]
     */
    public function getItemsByProductType(ProductType $productType): array
    {
        return array_filter($this->getItems(), function (OrderItem $orderItem) use ($productType) {
            return $orderItem->getProductType() === $productType;
        });
    }

    /**
     * Do not use this method! Order has N transports, not just exactly one.
     * However this method has to return valid value, because it is called in OrderFormType in vendor.
     * Overriding of OrderFormType is impossible and FormExtension can not disable this calling.
     * Removing of this calling needs a lot of copy-paste code or removing order detail page in administration.
     *
     * @deprecated
     * @internal
     * @return \App\Model\Transport\Transport
     */
    public function getTransport()
    {
        foreach ($this->getItems() as $item) {
            if ($item->isTypeTransport() === true) {
                return $item->getTransport();
            }
        }

        throw new \RuntimeException('Do not use this method! Order has N transports, and this order has no one. Read comment for method Order::getTransport()');
    }

    /**
     * @return \App\Model\Transport\Transport[]
     */
    public function getTransports(): array
    {
        $transports = [];
        foreach ($this->getItems() as $item) {
            if ($item->isTypeTransport() === true) {
                $transport = $item->getTransport();
                $transports[$transport->getId()] = $transport;
            }
        }

        return $transports;
    }

    /**
     * @return \App\Model\Order\Item\OrderItem[]
     */
    public function getTransportAndPaymentItems(): array
    {
        $items = [];
        foreach ($this->getItems() as $item) {
            if ($item->isTypeTransport() === true || $item->isTypePayment() === true) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
