<?php

namespace Shopsys\FrameworkBundle\Model\Localization\Exception;

use Exception;

class UnsupportedCurrencyException extends Exception implements LocalizationException
{
    public function __construct(string $currencyCode, Exception $previous = null)
    {
        $message = sprintf('Currency code %s is not supported', $currencyCode);
        parent::__construct($message, 0, $previous);
    }
}
