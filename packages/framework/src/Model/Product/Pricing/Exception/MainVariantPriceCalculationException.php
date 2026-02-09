<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing\Exception;

use Exception;

class MainVariantPriceCalculationException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
