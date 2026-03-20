<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class OrderPriceCalculation
{
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly Rounding $rounding,
        protected readonly OrderRoundingTypeEnum $orderRoundingTypeEnum,
    ) {
    }

    public function getOrderTotalPrice(Order $order): OrderTotalPriceInterface
    {
        $priceWithVat = Money::zero();
        $priceWithoutVat = Money::zero();
        $productPriceWithVat = Money::zero();
        $productPriceWithoutVat = Money::zero();

        foreach ($order->getItems() as $orderItem) {
            $itemTotalPrice = $this->orderItemPriceCalculation->calculateTotalPrice($orderItem);

            $priceWithVat = $priceWithVat->add($itemTotalPrice->getPriceWithVat());
            $priceWithoutVat = $priceWithoutVat->add($itemTotalPrice->getPriceWithoutVat());

            if (!($orderItem->isTypeProduct() || $orderItem->isTypeProductGift())) {
                continue;
            }

            $productPriceWithVat = $productPriceWithVat->add($itemTotalPrice->getPriceWithVat());
            $productPriceWithoutVat = $productPriceWithoutVat->add($itemTotalPrice->getPriceWithoutVat());
        }

        return new OrderTotalPrice($priceWithVat, $priceWithoutVat, $productPriceWithVat, $productPriceWithoutVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    public function calculateOrderRoundingPrice(
        Payment $payment,
        string $currencyRoundingType,
        PriceInterface $orderTotalPrice,
        int $domainId,
    ): ?PriceInterface {
        if (!$payment->hasOrderRoundingForDomain($domainId)) {
            return null;
        }

        $orderRoundingType = $payment->getOrderRoundingTypeForDomain($domainId);

        $priceWithVat = $orderTotalPrice->getPriceWithVat();
        $roundedPriceWithVat = $this->orderRoundingTypeEnum->roundPrice($orderRoundingType, $priceWithVat);

        $roundingPrice = $this->rounding->roundPriceWithVat(
            $roundedPriceWithVat->subtract($priceWithVat),
            $currencyRoundingType,
        );

        if ($roundingPrice->isZero()) {
            return null;
        }

        return new Price($roundingPrice, $roundingPrice);
    }
}
