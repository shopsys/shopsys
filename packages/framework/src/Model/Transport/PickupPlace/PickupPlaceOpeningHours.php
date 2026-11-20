<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\PickupPlace;

use DateTimeImmutable;

class PickupPlaceOpeningHours
{
    protected const string DATE_FORMAT = 'Y-m-d';

    /**
     * @param array<int, string[]> $closingTimesIndexedByDayOfWeek
     * @param array<int, array{from: string, to: string, closingTimes: string[]}> $exceptionDays
     */
    public function __construct(
        protected readonly array $closingTimesIndexedByDayOfWeek,
        protected readonly array $exceptionDays,
    ) {
    }

    public function isOpenOnDate(DateTimeImmutable $date): bool
    {
        return $this->getClosingTimesOnDate($date) !== [];
    }

    public function isOpenAfterTimeOnDate(DateTimeImmutable $date, string $time): bool
    {
        return array_any(
            $this->getClosingTimesOnDate($date),
            static fn (string $closingTime): bool => $closingTime > $time,
        );
    }

    /**
     * @return string[]
     */
    protected function getClosingTimesOnDate(DateTimeImmutable $date): array
    {
        $formattedDate = $date->format(static::DATE_FORMAT);

        foreach ($this->exceptionDays as $exceptionDay) {
            if ($exceptionDay['from'] <= $formattedDate && $formattedDate <= $exceptionDay['to']) {
                return $exceptionDay['closingTimes'];
            }
        }

        return $this->closingTimesIndexedByDayOfWeek[(int)$date->format('N')] ?? [];
    }
}
