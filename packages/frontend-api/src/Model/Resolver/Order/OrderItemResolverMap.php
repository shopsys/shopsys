<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;

class OrderItemResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'OrderItem' => [
                'totalPrice' => function (OrderItem $orderItem) {
                    return $this->orderItemPriceCalculation->calculateTotalPrice($orderItem);
                },
                'unitPrice' => function (OrderItem $orderItem) {
                    return $orderItem->getPrice();
                },
                'unit' => function (OrderItem $orderItem) {
                    return $orderItem->getUnitName();
                },
                'vatRate' => function (OrderItem $orderItem) {
                    return $orderItem->getVatPercent();
                },
                'product' => function (OrderItem $orderItem) {
                    if ($orderItem->isTypeProduct()) {
                        return $orderItem->getProduct();
                    }

                    if ($orderItem->isTypeProductGift()) {
                        return $orderItem->getProductGift();
                    }

                    return null;
                },
                'transport' => function (OrderItem $orderItem) {
                    if ($orderItem->isTypeTransport()) {
                        return $orderItem->getTransport();
                    }

                    return null;
                },
                'payment' => function (OrderItem $orderItem) {
                    if ($orderItem->isTypePayment()) {
                        return $orderItem->getPayment();
                    }

                    return null;
                },
            ],
        ];
    }
}
