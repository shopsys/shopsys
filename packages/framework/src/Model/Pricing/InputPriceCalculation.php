<?php

namespace Shopsys\FrameworkBundle\Model\Pricing;

class InputPriceCalculation
{
    /**
     * @param int $inputPriceType
     * @param string $basePriceWithVat
     * @param string $vatPercent
     */
    public function getInputPrice($inputPriceType, $basePriceWithVat, $vatPercent): string
    {
        if ($inputPriceType === PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT) {
            $inputPrice = $this->getInputPriceWithoutVat(
                $basePriceWithVat,
                $vatPercent
            );
        } elseif ($inputPriceType === PricingSetting::INPUT_PRICE_TYPE_WITH_VAT) {
            $inputPrice = $basePriceWithVat;
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException(
                sprintf('Input price type "%s" is not valid', $inputPriceType)
            );
        }

        return round($inputPrice, 6);
    }

    /**
     * @param string $basePriceWithVat
     * @param string $vatPercent
     */
    private function getInputPriceWithoutVat($basePriceWithVat, $vatPercent): string
    {
        return 100 * $basePriceWithVat / (100 + $vatPercent);
    }
}
