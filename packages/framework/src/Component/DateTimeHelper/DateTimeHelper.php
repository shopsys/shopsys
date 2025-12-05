<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTimeZone;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Clock\DatePoint;

class DateTimeHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param string $format
     * @param string $time
     * @return \Symfony\Component\Clock\DatePoint
     */
    public static function createFromFormat(string $format, string $time): DatePoint
    {
        $dateTime = DatePoint::createFromFormat($format, $time);

        // @phpstan-ignore identical.alwaysFalse (DateTimeImmutable::createFromFormat can return false on invalid format)
        if ($dateTime === false) {
            throw new CannotParseDateTimeException($format, $time);
        }

        return $dateTime;
    }

    /**
     * @param int $intervalInMinutes
     * @param \DateTimeZone $dateTimeZone
     * @return \Symfony\Component\Clock\DatePoint
     */
    public function getCurrentRoundedTimeForIntervalAndTimezone(
        int $intervalInMinutes,
        DateTimeZone $dateTimeZone,
    ): DatePoint {
        $time = new DatePoint('now', $dateTimeZone);
        $time = $time->modify('-' . $time->format('s') . ' sec');
        $time = $time->modify('-' . ($time->format('i') % $intervalInMinutes) . ' min');

        return $time;
    }

    /**
     * @param string $hoursAndMinutes
     * @return \Symfony\Component\Clock\DatePoint
     */
    public function createDateTimeFromTime(string $hoursAndMinutes): DatePoint
    {
        return new DatePoint(sprintf('1970-01-01 %s:00', $hoursAndMinutes));
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getCurrentDayOfWeek(int $domainId): int
    {
        return (int)$this->clock->now()->setTimezone(
            $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId),
        )->format('N');
    }

    /**
     * @param string $dateTimeString
     * @param \DateTimeZone $dateTimeZone
     * @return \Symfony\Component\Clock\DatePoint
     */
    public function createUtcDateTimeByTimeZoneAndString(
        string $dateTimeString,
        DateTimeZone $dateTimeZone,
    ): DatePoint {
        return (new DatePoint($dateTimeString, $dateTimeZone))->setTimezone(new DateTimeZone('UTC'));
    }
}
