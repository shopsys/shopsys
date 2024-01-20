<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;

class DateTimeHelper
{
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

    /**
     * @param string $dateTime
     * @param \DateTimeZone $inputTimeZone
     * @return \DateTimeImmutable
     */
    public static function convertDateTimeFromTimezoneToUtc(
        string $dateTime,
        DateTimeZone $inputTimeZone,
    ): DateTimeImmutable {
        $utcTimeZone = new DateTimeZone('UTC');

        return (new DateTimeImmutable($dateTime, $inputTimeZone))->setTimezone($utcTimeZone);
    }

    /**
     * @param int $dayNumber
     * @param \DateTimeZone $inputTimeZone
     * @return \DateTimeImmutable
     */
    public static function getUtcDateForDayInCurrentWeek(
        int $dayNumber,
        DateTimeZone $inputTimeZone,
    ): DateTimeImmutable {
        $dayName = match ($dayNumber) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
            default => throw new InvalidArgumentException(sprintf('Day number "%s" is not valid. (expected a value in range 1-7)', $dayNumber)),
        };

        return self::convertDateTimeFromTimezoneToUtc('this week ' . $dayName, $inputTimeZone);
    }

    /**
     * @param string $hoursAndMinutes
     * @return \DateTimeImmutable
     */
    public static function createDateTimeFromTime(string $hoursAndMinutes): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('1970-01-01 %s:00', $hoursAndMinutes));
    }

    /**
     * @param string|null $hoursAndMinutes
     * @param int $domainId
     * @return string|null
     */
    public function convertHoursAndMinutesFromUtcToDisplayTimezone(?string $hoursAndMinutes, int $domainId): ?string
    {
        if ($hoursAndMinutes === null) {
            return null;
        }

        $displayTimeZone = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId);
        $dateTime = self::createDateTimeFromTime($hoursAndMinutes);

        return $dateTime->setTimezone($displayTimeZone)->format('H:i');
    }
}
