<?php

namespace Shopsys\FrameworkBundle\Component\Cron\Config\Exception;

use Exception;

class InvalidTimeFormatException extends Exception implements CronConfigException
{
    public function __construct(string $timeString, int $maxValue, int $divisibleBy, Exception $previous = null)
    {
        parent::__construct(
            'Time configuration "' . $timeString . '" is invalid. '
            . 'Must by divisible by ' . $divisibleBy . ' and less or equal than ' . $maxValue,
            0,
            $previous
        );
    }
}
