<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\InvalidCurrencyRoundingTypeException;

class Rounding
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    protected $pricingSetting;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(PricingSetting $pricingSetting)
    {
        $this->pricingSetting = $pricingSetting;
    }

    /**
     * @deprecated Will be removed in the next major release, use Rounding::roundPriceWithVatByCurrency instead
     *
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function roundPriceWithVat(Money $priceWithVat): Money
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the Rounding::roundPriceWithVatByCurrency instead.', __METHOD__), E_USER_DEPRECATED);

        $roundingType = $this->pricingSetting->getRoundingType();

        switch ($roundingType) {
            case PricingSetting::ROUNDING_TYPE_HUNDREDTHS:
                return $priceWithVat->round(2);

            case PricingSetting::ROUNDING_TYPE_FIFTIES:
                return $priceWithVat->multiply(2)->round(0)->divide(2, 1);

            case PricingSetting::ROUNDING_TYPE_INTEGER:
                return $priceWithVat->round(0);

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidRoundingTypeException(
                    sprintf('Rounding type %s is not valid', $roundingType)
                );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function roundPriceWithVatByCurrency(Money $priceWithVat, Currency $currency): Money
    {
        $roundingType = $currency->getRoundingType();
        switch ($roundingType) {
            case Currency::ROUNDING_TYPE_HUNDREDTHS:
                return $priceWithVat->round(2);

            case Currency::ROUNDING_TYPE_FIFTIES:
                return $priceWithVat->multiply(2)->round(0)->divide(2, 1);

            case Currency::ROUNDING_TYPE_INTEGER:
                return $priceWithVat->round(0);

            default:
                throw new InvalidCurrencyRoundingTypeException($roundingType);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function roundPriceWithoutVat(Money $priceWithoutVat): Money
    {
        return $priceWithoutVat->round(2);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $vatAmount
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function roundVatAmount(Money $vatAmount): Money
    {
        return $vatAmount->round(2);
    }
}
