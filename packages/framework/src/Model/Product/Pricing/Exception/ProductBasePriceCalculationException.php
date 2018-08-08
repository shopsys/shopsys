<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing\Exception;

use Exception;

class ProductBasePriceCalculationException extends Exception implements ProductPricingException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
