<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class OrderPriceCalculation
{
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly Rounding $rounding,
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
        bool $paymentCzkRounding,
        string $currencyCode,
        string $currencyRoundingType,
        PriceInterface $orderTotalPrice,
    ): ?PriceInterface {
        if (!$paymentCzkRounding || $currencyCode !== Currency::CODE_CZK) {
            return null;
        }

        $priceWithVat = $orderTotalPrice->getPriceWithVat();
        $roundedPriceWithVat = $priceWithVat->round(0);

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
