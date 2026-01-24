<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Exception;

use Exception;

class InvalidQuantityException extends Exception
{
    public function __construct(protected mixed $invalidValue, string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }
}
