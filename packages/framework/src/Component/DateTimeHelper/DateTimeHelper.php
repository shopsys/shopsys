<?php

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTime;

class DateTimeHelper
{
    public static function createTodayMidnightDateTime(): \DateTime
    {
        $todayMidnight = new DateTime();
        $todayMidnight->setTime(0, 0, 0);

        return $todayMidnight;
    }

    public static function createFromFormat(string $format, string $time): \DateTime
    {
        $dateTime = DateTime::createFromFormat($format, $time);

        if ($dateTime === false) {
            throw new \Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException($format, $time);
        }

        return $dateTime;
    }
}
