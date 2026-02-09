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
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function createOrderItem(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        $entityClassName = $this->entityNameResolver->resolve(OrderItem::class);

        return new $entityClassName(
            $order,
            $orderItemData->name,
            new Price($orderItemData->unitPriceWithoutVat, $orderItemData->unitPriceWithVat),
            $orderItemData->vatPercent,
            $orderItemData->quantity,
            $orderItemData->type,
            $orderItemData->unitName,
            $orderItemData->catnum,
        );
    }

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

    public function createProductGift(
        OrderItemData $orderItemData,
        Order $order,
        ?Product $product,
    ): OrderItem {
        $orderItem = $this->createOrderItem(
            $orderItemData,
            $order,
        );

        $orderItem->setProductGift($product);

        return $orderItem;
    }

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

    public function createDiscount(
        OrderItemData $orderItemData,
        Order $order,
    ): OrderItem {
        return $this->createOrderItem(
            $orderItemData,
            $order,
        );
    }

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
