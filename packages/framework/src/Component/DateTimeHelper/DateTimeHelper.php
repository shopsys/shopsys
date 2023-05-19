<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTime;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException;

class DateTimeHelper
{
    /**
     * @return \DateTime
     */
    public static function createTodayMidnightDateTime()
    {
        $todayMidnight = new DateTime();
        $todayMidnight->setTime(0, 0, 0);

        return $todayMidnight;
    }

    /**
     * @param string $format
     * @param string $time
     * @return \DateTime
     */
    public static function createFromFormat($format, $time)
    {
        $dateTime = DateTime::createFromFormat($format, $time);

        if ($dateTime === false) {
            throw new CannotParseDateTimeException($format, $time);
        }

        return $dateTime;
    }
}
