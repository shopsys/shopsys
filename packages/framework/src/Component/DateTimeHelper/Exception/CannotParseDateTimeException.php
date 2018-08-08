<?php

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception;

use Exception;

class CannotParseDateTimeException extends Exception
{
    public function __construct(string $format, string $time, Exception $previous = null)
    {
        $message = sprintf(
            'Cannot parse string %s using format %s as DateTime.',
            var_export($time, true),
            var_export($format, true)
        );

        parent::__construct($message, 0, $previous);
    }
}
