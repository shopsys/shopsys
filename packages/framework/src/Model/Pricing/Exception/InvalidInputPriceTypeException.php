<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;

class InvalidInputPriceTypeException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
