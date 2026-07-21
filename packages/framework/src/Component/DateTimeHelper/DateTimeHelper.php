<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DateTimeHelper;

use DateTimeInterface;
use DateTimeZone;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\Exception\CannotParseDateTimeException;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Symfony\Component\Clock\DatePoint;

class DateTimeHelper
{
    public function __construct(
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly ClockInterface $clock,
    ) {
    }

    public static function createFromFormat(string $format, string $time): DatePoint
    {
        $dateTime = DatePoint::createFromFormat($format, $time);

        // @phpstan-ignore identical.alwaysFalse (DateTimeImmutable::createFromFormat can return false on invalid format)
        if ($dateTime === false) {
            throw new CannotParseDateTimeException($format, $time);
        }

        return $dateTime;
    }

    public function getCurrentRoundedTimeForIntervalAndTimezone(
        int $intervalInMinutes,
        DateTimeZone $dateTimeZone,
    ): DatePoint {
        $time = new DatePoint('now', $dateTimeZone);
        $time = $time->modify('-' . $time->format('s') . ' sec');
        $time = $time->modify('-' . ($time->format('i') % $intervalInMinutes) . ' min');

        return $time;
    }

    public function createDateTimeFromTime(string $hoursAndMinutes): DatePoint
    {
        return new DatePoint(sprintf('1970-01-01 %s:00', $hoursAndMinutes));
    }

    public function getCurrentDayOfWeek(int $domainId): int
    {
        return (int)$this->clock->now()->setTimezone(
            $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainId),
        )->format('N');
    }

    public function createUtcDateTimeByTimeZoneAndString(
        string $dateTimeString,
        DateTimeZone $dateTimeZone,
    ): DatePoint {
        return (new DatePoint($dateTimeString, $dateTimeZone))->setTimezone(new DateTimeZone('UTC'));
    }

    public static function isWeekend(DateTimeInterface $date): bool
    {
        return (int)$date->format('N') >= 6;
    }
}
