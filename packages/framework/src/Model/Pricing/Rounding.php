<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

class Rounding
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    public function __construct(PricingSetting $pricingSetting)
    {
        $this->pricingSetting = $pricingSetting;
    }
    
    public function roundPriceWithVat(string $priceWithVat): string
    {
        $roundingType = $this->pricingSetting->getRoundingType();

        switch ($roundingType) {
            case PricingSetting::ROUNDING_TYPE_HUNDREDTHS:
                $roundedPriceWithVat = round($priceWithVat, 2);
                break;

            case PricingSetting::ROUNDING_TYPE_FIFTIES:
                $roundedPriceWithVat = round($priceWithVat * 2, 0) / 2;
                break;

            case PricingSetting::ROUNDING_TYPE_INTEGER:
                $roundedPriceWithVat = round($priceWithVat, 0);
                break;

            default:
                throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidRoundingTypeException(
                    sprintf('Rounding type %s is not valid', $roundingType)
                );
        }

        return $roundedPriceWithVat;
    }
    
    public function roundPriceWithoutVat(string $priceWithoutVat): string
    {
        return round($priceWithoutVat, 2);
    }
    
    public function roundVatAmount(string $vatAmount): string
    {
        return round($vatAmount, 2);
    }
}
