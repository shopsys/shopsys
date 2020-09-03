<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use LogicException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class CurrencyMismatchException extends LogicException
{
    public function __construct(Currency $expectedCurrency, Currency $providedCurrency)
    {
        $message = sprintf(
            'Provided currency "%s" is not same as expected one "%s".',
            $providedCurrency->getCode(),
            $expectedCurrency->getCode()
        );
        parent::__construct($message);
    }
}
