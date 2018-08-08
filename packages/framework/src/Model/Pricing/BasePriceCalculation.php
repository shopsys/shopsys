<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class BasePriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation
     */
    private $priceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    public function __construct(PriceCalculation $priceCalculation, Rounding $rounding)
    {
        $this->priceCalculation = $priceCalculation;
        $this->rounding = $rounding;
    }
    
    public function calculateBasePrice(string $inputPrice, int $inputPriceType, Vat $vat): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        $basePriceWithVat = $this->getBasePriceWithVat($inputPrice, $inputPriceType, $vat);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($basePriceWithVat, $vat);
        $basePriceWithoutVat = $this->rounding->roundPriceWithoutVat($basePriceWithVat - $vatAmount);

        return new Price($basePriceWithoutVat, $basePriceWithVat);
    }

    /**
     * @param string[] $coefficients
     */
    public function applyCoefficients(Price $price, Vat $vat, $coefficients): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        $priceWithVatBeforeRounding = $price->getPriceWithVat();
        foreach ($coefficients as $coefficient) {
            $priceWithVatBeforeRounding *= $coefficient;
        }
        $priceWithVat = $this->rounding->roundPriceWithVat($priceWithVatBeforeRounding);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($priceWithVat, $vat);
        $priceWithoutVat = $this->rounding->roundPriceWithoutVat($priceWithVat - $vatAmount);

        return new Price($priceWithoutVat, $priceWithVat);
    }
    
    private function getBasePriceWithVat(string $inputPrice, int $inputPriceType, Vat $vat): string
    {
        switch ($inputPriceType) {
            case PricingSetting::INPUT_PRICE_TYPE_WITH_VAT:
                return $this->rounding->roundPriceWithVat($inputPrice);

            case PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT:
                return $this->rounding->roundPriceWithVat($this->priceCalculation->applyVatPercent($inputPrice, $vat));

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException();
        }
    }
}
