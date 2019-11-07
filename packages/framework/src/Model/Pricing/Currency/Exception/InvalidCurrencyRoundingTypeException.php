<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception;

use Exception;

class InvalidCurrencyRoundingTypeException extends Exception implements CurrencyException
{
    /**
     * @param string $roundingType
     * @param \Exception|null $previous
     */
    public function __construct(string $roundingType, ?Exception $previous = null)
    {
        $message = sprintf('Currency rounding type `%s` is not valid', $roundingType);
        parent::__construct($message, 0, $previous);
    }
}
