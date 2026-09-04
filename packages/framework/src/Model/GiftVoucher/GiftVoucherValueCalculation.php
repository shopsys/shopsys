<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use Shopsys\FrameworkBundle\Component\Money\Money;

class GiftVoucherValueCalculation
{
    protected const int VALUE_CALCULATION_SCALE = 6;

    public function calculateValueWithoutVat(GiftVoucher $giftVoucher): Money
    {
        $vatCoefficient = (string)(1 + (float)$giftVoucher->getVatPercent() / 100);

        return $giftVoucher->getValueWithVat()->divide($vatCoefficient, static::VALUE_CALCULATION_SCALE);
    }
}
