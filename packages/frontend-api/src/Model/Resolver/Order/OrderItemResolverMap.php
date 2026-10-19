<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductEntityQuery;

class OrderItemResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly DataLoaderInterface $orderItemRelatedItemsBatchLoader,
        protected readonly ProductEntityQuery $productEntityQuery,
        protected readonly DataLoaderInterface $orderItemRemainingComplaintQuantityBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'OrderItem' => [
                'remainingComplaintQuantity' => function (OrderItem $orderItem) {
                    return $this->orderItemRemainingComplaintQuantityBatchLoader->load($orderItem);
                },
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
                'deliveryDaysExtension' => function (OrderItem $orderItem) {
                    if (!$orderItem->isTypeAdditionalService()) {
                        return null;
                    }

                    return $orderItem->getAdditionalService()?->getDeliveryDaysExtension();
                },
                'product' => function (OrderItem $orderItem) {
                    if ($orderItem->isTypeProduct()) {
                        return $this->productEntityQuery->productByEntityQuery($orderItem->getProduct());
                    }

                    if ($orderItem->isTypeProductGift()) {
                        return $this->productEntityQuery->productByEntityQuery($orderItem->getProductGift());
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
                'relatedItems' => function (OrderItem $orderItem) {
                    return $this->orderItemRelatedItemsBatchLoader->load($orderItem);
                },
            ],
        ];
    }
}
