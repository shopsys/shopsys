<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Price;

use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyFormatterHelper
{
    protected const int MAX_FRACTION_DIGITS = 6;
    protected const string DECIMAL_POINT = '.';
    protected const string THOUSANDS_SEPARATOR = '';

    public function formatWithMaxFractionDigits(Money $money): string
    {
        return number_format(
            (float)$money->getAmount(),
            static::MAX_FRACTION_DIGITS,
            static::DECIMAL_POINT,
            static::THOUSANDS_SEPARATOR,
        );
    }
}
