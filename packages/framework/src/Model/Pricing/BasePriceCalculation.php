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
        Currency $currency,
    ): PriceInterface {
        switch ($inputPriceType) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $basePriceWithVat = $this->rounding->roundPriceWithVatByCurrency($inputPrice, $currency);
                $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat, $currency);
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount), $currency);

                return new Price($basePriceWithoutVat, $basePriceWithVat);

            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($inputPrice, $currency);
                $basePriceWithVat = $this->priceCalculation->applyVatPercent($basePriceWithoutVat, $vat);
                $basePriceWithVat = $this->rounding->roundPriceWithVatByCurrency($basePriceWithVat, $currency);

                return new Price($basePriceWithoutVat, $basePriceWithVat);

            default:
                throw new InvalidInputPriceTypeException();
        }
    }
}
