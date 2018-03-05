<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class InvalidRoundingTypeException extends Exception implements PricingException
{
    /**
     * @param string $message
     * @param \Exception|null $previous
     */
    public function __construct($message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
