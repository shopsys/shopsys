<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

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

                    return null;
                },
                'paidQuantity' => function (OrderItem $orderItem) {
                    $product = $orderItem->getProduct();
                    if ($product === null || !$orderItem->isTypeProduct()) {
                        return $orderItem->getQuantity();
                    }

                    $promotionX = $product->getPromotionX();
                    $promotionY = $product->getPromotionY();

                    if ($promotionX === null || $promotionY === null) {
                        return $orderItem->getQuantity();
                    }

                    $quantity = $orderItem->getQuantity();
                    $fullPromotion = $promotionX + $promotionY;
                    $fullGroups = intdiv($quantity, $fullPromotion);
                    $remainder = $quantity % $fullPromotion;
                    $extra = max(0, min($remainder - $promotionX, $promotionY));
                    $freeQuantity = $fullGroups * $promotionY + $extra;

                    return $quantity - $freeQuantity;
                },
                'freeQuantity' => function (OrderItem $orderItem) {
                    $product = $orderItem->getProduct();
                    if ($product === null || !$orderItem->isTypeProduct()) {
                        return 0;
                    }

                    $promotionX = $product->getPromotionX();
                    $promotionY = $product->getPromotionY();

                    if ($promotionX === null || $promotionY === null) {
                        return 0;
                    }

                    $quantity = $orderItem->getQuantity();
                    $fullPromotion = $promotionX + $promotionY;
                    $fullGroups = intdiv($quantity, $fullPromotion);
                    $remainder = $quantity % $fullPromotion;
                    $extra = max(0, min($remainder - $promotionX, $promotionY));

                    return $fullGroups * $promotionY + $extra;
                },
                'unitPriceBeforePromotion' => function (OrderItem $orderItem) {
                    $product = $orderItem->getProduct();
                    if ($product === null || !$orderItem->isTypeProduct()) {
                        return null;
                    }

                    $promotionX = $product->getPromotionX();
                    $promotionY = $product->getPromotionY();

                    if ($promotionX === null || $promotionY === null) {
                        return null;
                    }

                    $quantity = $orderItem->getQuantity();
                    $fullPromotion = $promotionX + $promotionY;
                    $fullGroups = intdiv($quantity, $fullPromotion);
                    $remainder = $quantity % $fullPromotion;
                    $extra = max(0, min($remainder - $promotionX, $promotionY));
                    $freeQuantity = $fullGroups * $promotionY + $extra;

                    if ($freeQuantity === 0) {
                        return null;
                    }

                    $totalPriceWithVat = $this->orderItemPriceCalculation->calculateTotalPrice($orderItem)->getPriceWithVat();
                    $totalPriceWithoutVat = $this->orderItemPriceCalculation->calculateTotalPrice($orderItem)->getPriceWithoutVat();
                    $unitPrice = $orderItem->getPrice();

                    $originalTotalWithVat = $unitPrice->getPriceWithVat()->multiply($quantity);
                    $originalTotalWithoutVat = $unitPrice->getPriceWithoutVat()->multiply($quantity);

                    return new Price($unitPrice->getPriceWithoutVat(), $unitPrice->getPriceWithVat());
                },
            ],
        ];
    }
}
