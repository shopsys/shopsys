<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money\Exception;

use Throwable;

class InvalidNumericArgumentException extends MoneyException
{
    public function __construct(string $value, Throwable $previous)
    {
        $message = sprintf('Invalid numeric argument: "%s"', $value);

        parent::__construct($message, 0, $previous);
    }
}
