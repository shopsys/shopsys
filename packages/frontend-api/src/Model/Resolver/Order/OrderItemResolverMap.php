<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderItemResolverMap extends ResolverMap
{
    protected OrderItemPriceCalculation $orderItemPriceCalculation;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     */
    public function __construct(OrderItemPriceCalculation $orderItemPriceCalculation)
    {
        $this->orderItemPriceCalculation = $orderItemPriceCalculation;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'OrderItem' => [
                'totalPrice' => function (OrderItem $orderItem) {
                    return $this->orderItemPriceCalculation->calculateTotalPrice($orderItem);
                },
                'unitPrice' => function (OrderItem $orderItem) {
                    return new Price($orderItem->getPriceWithoutVat(), $orderItem->getPriceWithVat());
                },
                'unit' => function (OrderItem $orderItem) {
                    return $orderItem->getUnitName();
                },
                'vatRate' => function (OrderItem $orderItem) {
                    return $orderItem->getVatPercent();
                },
            ],
        ];
    }
}
