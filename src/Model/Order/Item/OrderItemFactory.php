<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use App\Model\Order\Order;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem as BaseOrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory as BaseOrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderItemFactory extends BaseOrderItemFactory
{
    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param string|null $unitName
     * @param string|null $catnum
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createProduct(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        ?string $unitName,
        ?string $catnum,
        ?BaseProduct $product = null
    ): BaseOrderItem {
        throw new \BadMethodCallException('Use ' . self::class . '::createProductByOrderItemData() instead of this method');
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createPayment(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Payment $payment
    ): BaseOrderItem {
        throw new \BadMethodCallException('Use ' . self::class . '::createPaymentByOrderItemData() instead of this method');
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $name
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param string $vatPercent
     * @param int $quantity
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createTransport(
        BaseOrder $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Transport $transport
    ): BaseOrderItem {
        throw new \BadMethodCallException('Use ' . self::class . '::createTransportByOrderItemData() instead of this method');
    }

    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Product\Product|null $product
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createProductByOrderItemData(OrderItemData $orderItemData, Order $order, ?Product $product): OrderItem
    {
        /** @var \App\Model\Order\Item\OrderItem $orderItem */
        $orderItem = parent::createProduct(
            $order,
            $orderItemData->name,
            new Price($orderItemData->priceWithoutVat, $orderItemData->priceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->unitName,
            $orderItemData->catnum,
            $product
        );
        $orderItem->setProductType($orderItemData->productType);
        $orderItem->setPromoCodeIdentifier($orderItemData->promoCodeIdentifier);

        return $orderItem;
    }

    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createPaymentByOrderItemData(OrderItemData $orderItemData, Order $order): OrderItem
    {
        /** @var \App\Model\Order\Item\OrderItem $orderItem */
        $orderItem = parent::createPayment(
            $order,
            $orderItemData->name,
            new Price($orderItemData->priceWithoutVat, $orderItemData->priceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->payment
        );
        $orderItem->setProductType($orderItemData->productType);

        return $orderItem;
    }

    /**
     * @param \App\Model\Order\Item\OrderItemData $orderItemData
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Order\Item\OrderItem
     */
    public function createTransportByOrderItemData(OrderItemData $orderItemData, Order $order): OrderItem
    {
        /** @var \App\Model\Order\Item\OrderItem $orderItem */
        $orderItem = parent::createTransport(
            $order,
            $orderItemData->name,
            new Price($orderItemData->priceWithoutVat, $orderItemData->priceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->transport
        );
        $orderItem->setProductType($orderItemData->productType);
        $orderItem->setPersonalPickupStock($orderItemData->personalPickupStock);

        return $orderItem;
    }
}
