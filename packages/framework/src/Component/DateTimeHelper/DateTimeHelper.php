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
     * @param string $hoursAndMinutes
     * @return \DateTimeImmutable
     */
    public static function createDateTimeFromTime(string $hoursAndMinutes): DateTimeImmutable
    {
        return new DateTimeImmutable(sprintf('1970-01-01 %s:00', $hoursAndMinutes));
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getCurrentDayOfWeek(int $domainId): int
    {
        return (int)(new DateTimeImmutable(
            'now',
            $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId),
        ))->format('N');
    }
}
