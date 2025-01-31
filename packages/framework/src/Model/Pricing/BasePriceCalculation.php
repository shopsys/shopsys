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
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateRoundedBasePrice(
        Money $inputPrice,
        int $inputPriceType,
        Vat $vat,
        Currency $currency,
    ): PriceInterface {
        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                $basePriceWithVat = $this->rounding->roundPriceWithVatByCurrency($inputPrice, $currency);
                $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat);
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount));

                return new Price($basePriceWithoutVat, $basePriceWithVat);

            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($inputPrice);
                $basePriceWithVat = $this->priceCalculation->applyVatPercent($basePriceWithoutVat, $vat);
                $basePriceWithVat = $this->rounding->roundPriceWithVatByCurrency($basePriceWithVat, $currency);

                return new Price($basePriceWithoutVat, $basePriceWithVat);

            default:
                throw new InvalidInputPriceTypeException();
        }
    }
}
