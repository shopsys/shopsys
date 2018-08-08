<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception;

use Exception;

class DeletingNotAllowedToDeleteCurrencyException extends Exception implements CurrencyException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
