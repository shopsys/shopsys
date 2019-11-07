<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class BasePriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation
     */
    protected $priceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    protected $rounding;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Rounding $rounding
     */
    public function __construct(PriceCalculation $priceCalculation, Rounding $rounding)
    {
        $this->priceCalculation = $priceCalculation;
        $this->rounding = $rounding;
    }

    /**
     * @deprecated Will be removed in the next major release, use BasePriceCalculation::calculateBasePriceRoundedByCurrency instead
     *
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function calculateBasePrice(Money $inputPrice, int $inputPriceType, Vat $vat): Price
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the BasePriceCalculation::calculateBasePriceRoundedByCurrency instead.', __METHOD__), E_USER_DEPRECATED);

        $basePriceWithVat = $this->getBasePriceWithVat($inputPrice, $inputPriceType, $vat);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat);
        $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount));

        return new Price($basePriceWithoutVat, $basePriceWithVat);
    }

    /**
     * @deprecated method is deprecated and will be removed in the next major. This method is not used, only in tests
     *
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
        Currency $currency
    ): Price {
        $basePriceWithVat = $this->getBasePriceWithVatRoundedByCurrency($inputPrice, $inputPriceType, $vat, $currency);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat);
        $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat->subtract($vatAmount));

        return new Price($basePriceWithoutVat, $basePriceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param string[] $coefficients
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function applyCoefficients(Price $price, Vat $vat, array $coefficients): Price
    {
        $priceWithVatBeforeRounding = $price->getPriceWithVat();
        foreach ($coefficients as $coefficient) {
            $priceWithVatBeforeRounding = $priceWithVatBeforeRounding->multiply($coefficient);
        }
        $priceWithVat = $this->rounding->roundPriceWithVat($priceWithVatBeforeRounding);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $this->rounding->roundPriceWithoutVat($priceWithVat->subtract($vatAmount));

        return new Price($priceWithoutVat, $priceWithVat);
    }

    /**
     * @deprecated Will be removed in the next major release, use BasePriceCalculation::getBasePriceWithVatRoundedCurrency instead
     *
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getBasePriceWithVat(Money $inputPrice, int $inputPriceType, Vat $vat): Money
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the BasePriceCalculation::getBasePriceWithVatRoundedCurrency instead.', __METHOD__), E_USER_DEPRECATED);

        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return $this->rounding->roundPriceWithVat($inputPrice);

            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return $this->rounding->roundPriceWithVat($this->priceCalculation->applyVatPercent($inputPrice, $vat));

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException();
        }
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
        Currency $currency
    ): Money {
        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return $this->rounding->roundPriceWithVatByCurrency($inputPrice, $currency);

            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return $this->rounding->roundPriceWithVatByCurrency(
                    $this->priceCalculation->applyVatPercent($inputPrice, $vat),
                    $currency
                );

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException();
        }
    }
}
