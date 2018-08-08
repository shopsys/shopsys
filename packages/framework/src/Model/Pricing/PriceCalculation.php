<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class PriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Rounding
     */
    private $rounding;

    public function __construct(Rounding $rounding)
    {
        $this->rounding = $rounding;
    }
    
    public function getVatAmountByPriceWithVat(string $priceWithVat, Vat $vat): string
    {
        return $this->rounding->roundVatAmount(
            $priceWithVat * $this->getVatCoefficientByPercent($vat->getPercent())
        );
    }
    
    public function getVatCoefficientByPercent(string $vatPercent): string
    {
        $ratio = $vatPercent / (100 + $vatPercent);
        return round($ratio, 4);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function applyVatPercent(string $priceWithoutVat, Vat $vat): string
    {
        return $priceWithoutVat * (100 + $vat->getPercent()) / 100;
    }
}
