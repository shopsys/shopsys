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
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function createOrderItem(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        $entityClassName = $this->entityNameResolver->resolve(OrderItem::class);

        $orderItem = new $entityClassName(
            $order,
            $orderItemData->name,
            new Price($orderItemData->unitPriceWithoutVat, $orderItemData->unitPriceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->type,
            $orderItemData->unitName,
            $orderItemData->catnum,
        );

        if ($orderItemData->usePriceCalculation === false && $orderItemData->totalPriceWithVat !== null) {
            $orderItem->setTotalPrice(new Price($orderItemData->totalPriceWithoutVat, $orderItemData->totalPriceWithVat));
        }

        return $orderItem;
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
        $orderItem = $this->createOrderItem(
            $orderItemData,
            $order,
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
        $orderItem = $this->createOrderItem(
            $orderItemData,
            $order,
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
        $orderItem = $this->createOrderItem(
            $orderItemData,
            $order,
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
        return $this->createOrderItem(
            $orderItemData,
            $order,
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
        return $this->createOrderItem(
            $orderItemData,
            $order,
        );
    }
}
