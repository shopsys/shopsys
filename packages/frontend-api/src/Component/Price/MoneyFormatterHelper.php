<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Price;

use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyFormatterHelper
{
    protected const MAX_FRACTION_DIGITS = 6;
    protected const DECIMAL_POINT = '.';
    protected const THOUSANDS_SEPARATOR = '';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return string
     */
    public static function formatWithMaxFractionDigits(Money $money): string
    {
        return number_format(
            $money->getAmount(),
            static::MAX_FRACTION_DIGITS,
            static::DECIMAL_POINT,
            static::THOUSANDS_SEPARATOR
        );
    }
}
