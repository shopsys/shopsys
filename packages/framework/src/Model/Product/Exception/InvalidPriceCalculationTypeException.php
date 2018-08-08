<?php

namespace Shopsys\FrameworkBundle\Model\Product\Exception;

use Exception;

class InvalidPriceCalculationTypeException extends Exception implements ProductException
{
    public function __construct(string $priceCalculationType, Exception $previous = null)
    {
        $message = 'Price calculation type "' . $priceCalculationType . '" is not valid.';
        parent::__construct($message, 0, $previous);
    }
}
