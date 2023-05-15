<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;

class QuantifiedProductDiscountCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     */
    public function __construct(
        protected readonly PriceCalculation $priceCalculation,
        protected readonly Rounding $rounding,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param string $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price|null
     */
    protected function calculateDiscountRoundedByCurrency(
        QuantifiedItemPrice $quantifiedItemPrice,
        string $discountPercent,
        Currency $currency,
    ): ?Price {
        $vat = $quantifiedItemPrice->getVat();
        $multiplier = (string)((float)$discountPercent / 100);
        $priceWithVat = $this->rounding->roundPriceWithVatByCurrency(
            $quantifiedItemPrice->getTotalPrice()->getPriceWithVat()->multiply($multiplier),
            $currency,
        );

        if ($priceWithVat->isZero()) {
            return null;
        }

        $priceVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $priceWithVat->subtract($priceVatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemsPrices
     * @param string|null $discountPercent
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function calculateDiscountsRoundedByCurrency(array $quantifiedItemsPrices, ?string $discountPercent, Currency $currency): array
    {
        $quantifiedItemsDiscounts = [];

        foreach ($quantifiedItemsPrices as $quantifiedItemIndex => $quantifiedItemPrice) {
            if ($discountPercent === null) {
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = null;
            } else {
                $quantifiedItemsDiscounts[$quantifiedItemIndex] = $this->calculateDiscountRoundedByCurrency(
                    $quantifiedItemPrice,
                    $discountPercent,
                    $currency,
                );
            }
        }

        return $quantifiedItemsDiscounts;
    }
}
