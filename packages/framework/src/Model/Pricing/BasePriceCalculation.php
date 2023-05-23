<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class BasePriceCalculation
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
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateBasePriceRoundedByCurrency(
        Money $inputPrice,
        int $inputPriceType,
        Vat $vat,
        Currency $currency,
    ): Price {
        $basePriceWithVat = $this->getBasePriceWithVatRoundedByCurrency($inputPrice, $inputPriceType, $vat, $currency);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat);
        $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount));

        return new Price($basePriceWithoutVat, $basePriceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getBasePriceWithVatRoundedByCurrency(
        Money $inputPrice,
        int $inputPriceType,
        Vat $vat,
        Currency $currency,
    ): Money {
        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return $this->rounding->roundPriceWithVatByCurrency($inputPrice, $currency);

            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return $this->rounding->roundPriceWithVatByCurrency(
                    $this->priceCalculation->applyVatPercent($inputPrice, $vat),
                    $currency,
                );

            default:
                throw new InvalidInputPriceTypeException();
        }
    }
}
