<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class OrderItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $orderItemType
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    protected function doCreateOrderItem(
        OrderItemData $orderItemData,
        Order $order,
        string $orderItemType,
    ): OrderItem {
        $entityClassName = $this->entityNameResolver->resolve(OrderItem::class);

        return new $entityClassName(
            $order,
            $orderItemData->name,
            new Price($orderItemData->priceWithoutVat, $orderItemData->priceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemType,
            $orderItemData->unitName,
            $orderItemData->catnum,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createProduct(
        OrderItemData $orderItemData,
        Order $order,
        ?Product $product,
    ): OrderItem {
        $orderItem = $this->doCreateOrderItem(
            $orderItemData,
            $order,
            OrderItem::TYPE_PRODUCT,
        );

        $orderItem->setProduct($product);

        return $orderItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createTransport(
        OrderItemData $orderItemData,
        Order $order,
        Transport $transport,
    ): OrderItem {
        $orderItem = $this->doCreateOrderItem(
            $orderItemData,
            $order,
            OrderItem::TYPE_TRANSPORT,
        );

        $orderItem->setTransport($transport);

        return $orderItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createPayment(
        OrderItemData $orderItemData,
        Order $order,
        Payment $payment,
    ): OrderItem {
        $orderItem = $this->doCreateOrderItem(
            $orderItemData,
            $order,
            OrderItem::TYPE_PAYMENT,
        );

        $orderItem->setPayment($payment);

        return $orderItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createDiscount(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        return $this->doCreateOrderItem(
            $orderItemData,
            $order,
            OrderItem::TYPE_DISCOUNT,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createRounding(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        return $this->doCreateOrderItem(
            $orderItemData,
            $order,
            OrderItem::TYPE_ROUNDING,
        );
    }
}
