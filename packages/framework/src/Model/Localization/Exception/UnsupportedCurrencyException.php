<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization\Exception;

use Exception;

class UnsupportedCurrencyException extends Exception
{
    /**
     * @param string $currencyCode
     */
    public function __construct($currencyCode, ?Exception $previous = null)
    {
        $message = sprintf('Currency code %s is not supported', $currencyCode);

        parent::__construct($message, 0, $previous);
    }
}
