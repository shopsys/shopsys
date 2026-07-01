<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security\Exception;

use InvalidArgumentException;
use Throwable;

class InvalidJsonTokenDataException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid JSON data for TokensData', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
