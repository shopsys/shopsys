<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money\Exception;

use Throwable;
use function get_class;
use function gettype;
use function is_object;

class UnsupportedTypeException extends MoneyException
{
    /**
     * @param string[] $supportedTypes
     */
    public function __construct(mixed $value, array $supportedTypes, ?Throwable $previous = null)
    {
        $message = sprintf('Expected one of: "%s"', implode('", "', $supportedTypes));
        $message .= sprintf(', "%s" given.', is_object($value) ? get_class($value) : gettype($value));

        parent::__construct($message, 0, $previous);
    }
}
