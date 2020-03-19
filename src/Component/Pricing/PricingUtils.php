<?php

declare(strict_types=1);

namespace App\Component\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PricingUtils
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price1
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price2
     * @return bool
     */
    public static function equals(Price $price1, Price $price2): bool
    {
        return $price1->getPriceWithoutVat()->equals($price2->getPriceWithoutVat())
            && $price1->getPriceWithVat()->equals($price2->getPriceWithVat())
            && $price1->getVatAmount()->equals($price2->getVatAmount());
    }
}
