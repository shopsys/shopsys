<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception;

use Exception;

class VatMarkedAsDeletedDeleteException extends Exception implements VatException
{
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
