<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;

class InputPriceCalculation
{
    protected const INPUT_PRICE_SCALE = 6;

    public function getInputPrice(int $inputPriceType, Money $basePriceWithVat, string $vatPercent): Money
    {
        if ($inputPriceType === PricingSetting::PRICE_TYPE_WITHOUT_VAT) {
            $inputPrice = $this->getInputPriceWithoutVat($basePriceWithVat, $vatPercent);
        } elseif ($inputPriceType === PricingSetting::PRICE_TYPE_WITH_VAT) {
            $inputPrice = $basePriceWithVat->round(static::INPUT_PRICE_SCALE);
        } else {
            throw new InvalidInputPriceTypeException(
                sprintf('Input price type "%s" is not valid', $inputPriceType),
            );
        }

        return $inputPrice;
    }

    protected function getInputPriceWithoutVat(Money $basePriceWithVat, string $vatPercent): Money
    {
        $divisor = (string)(1 + (float)$vatPercent / 100);

        return $basePriceWithVat->divide($divisor, static::INPUT_PRICE_SCALE);
    }
}
