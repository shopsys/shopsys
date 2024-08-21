<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;

class OrderItemResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Overblog\DataLoader\DataLoaderInterface $firstImageBatchLoader
     */
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly DataLoaderInterface $firstImageBatchLoader,
    ) {
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
                    return $orderItem->getPrice();
                },
                'unit' => function (OrderItem $orderItem) {
                    return $orderItem->getUnitName();
                },
                'vatRate' => function (OrderItem $orderItem) {
                    return $orderItem->getVatPercent();
                },
                'productMainImage' => function (OrderItem $orderItem) {
                    if (!$orderItem->isTypeProduct() || !$orderItem->hasProduct()) {
                        return null;
                    }

                    return $this->firstImageBatchLoader->load(
                        new ImageBatchLoadData(
                            $orderItem->getProduct()->getId(),
                            'product',
                            null,
                        ),
                    );
                },
            ],
        ];
    }
}
