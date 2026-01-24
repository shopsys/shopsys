<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Exception;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException
{
    public function __construct(string $message = '', ?Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
