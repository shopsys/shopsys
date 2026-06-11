<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AddressCoordinates\Exception;

use Exception;
use Throwable;

class GoogleAddressCoordinatesException extends Exception
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
