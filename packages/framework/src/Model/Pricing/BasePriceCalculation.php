<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class BasePriceCalculation
{
    public function __construct(
        protected readonly PriceCalculation $priceCalculation,
        protected readonly Rounding $rounding,
    ) {
    }

    public function calculateRoundedBasePrice(
        Money $inputPrice,
        int $inputPriceType,
        Vat $vat,
        string $roundingType,
        int $roundingPlaces,
        ?Currency $currency = null,
    ): PriceInterface {
        switch ($inputPriceType) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $basePriceWithVat = $this->rounding->roundPriceWithVat($inputPrice, $roundingType);
                $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat, $roundingPlaces);
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount), $roundingPlaces);

                return new Price($basePriceWithoutVat, $basePriceWithVat, $currency);

            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($inputPrice, $roundingPlaces);
                $basePriceWithVat = $this->priceCalculation->applyVatPercent($basePriceWithoutVat, $vat);
                $basePriceWithVat = $this->rounding->roundPriceWithVat($basePriceWithVat, $roundingType);

                return new Price($basePriceWithoutVat, $basePriceWithVat, $currency);

            default:
                throw new InvalidInputPriceTypeException();
        }
    }
}
