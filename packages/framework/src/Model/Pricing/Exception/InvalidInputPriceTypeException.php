<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class InvalidInputPriceTypeException extends Exception implements PricingException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
