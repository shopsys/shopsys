<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;

class DateTimeHelper
{
    public const UTC_TIMEZONE = 'UTC';

    public const TIME_REGEX = '#^([01]?[0-9]|2[0-3]):[0-5][0-9]$#'; //hh:mm

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @param string $format
     * @param string $time
     * @return \DateTime
     */
    public static function createFromFormat(string $format, string $time): DateTime
    {
        $dateTime = DateTime::createFromFormat($format, $time);

        if ($dateTime === false) {
            throw new CannotParseDateTimeException($format, $time);
        }

        return $dateTime;
    }

    /**
     * @param string $original
     * @return \DateTime
     */
    public function convertDatetimeStringFromDisplayTimeZoneToUtc(string $original): DateTime
    {
        $dateTime = new DateTime($original, $this->displayTimeZoneProvider->getDisplayTimeZone());
        $dateTime->setTimezone(new DateTimeZone(self::UTC_TIMEZONE));

        return $dateTime;
    }

    /**
     * @param \DateTime $dateTime
     * @return \DateTime
     */
    public function convertDateTimeFromUtcToDisplayTimeZone(DateTime $dateTime): DateTime
    {
        $dateTime->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZone());

        return $dateTime;
    }

    /**
     * @param int $intervalInMinutes
     * @param \DateTimeZone $dateTimeZone
     * @return \DateTimeImmutable
     */
    public static function getCurrentRoundedTimeForIntervalAndTimezone(
        int $intervalInMinutes,
        DateTimeZone $dateTimeZone,
    ): DateTimeImmutable {
        $time = new DateTime('now', $dateTimeZone);
        $time->modify('-' . $time->format('s') . ' sec');
        $time->modify('-' . ($time->format('i') % $intervalInMinutes) . ' min');

        return DateTimeImmutable::createFromMutable($time);
    }
}
