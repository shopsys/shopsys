<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class CurrencyMismatchException extends Exception
{
    public function __construct(string $currencyCodeA, string $currencyCodeB, ?Exception $previous = null)
    {
        parent::__construct(
            sprintf('Cannot combine prices with different currencies "%s" and "%s".', $currencyCodeA, $currencyCodeB),
            0,
            $previous,
        );
    }
}
