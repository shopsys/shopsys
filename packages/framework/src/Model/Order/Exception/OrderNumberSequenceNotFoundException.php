<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Exception;

use Exception;

class OrderNumberSequenceNotFoundException extends Exception
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
