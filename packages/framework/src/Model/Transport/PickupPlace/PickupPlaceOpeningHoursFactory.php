<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\PickupPlace;

class PickupPlaceOpeningHoursFactory
{
    /**
     * @param array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>} $pickupPlaceOpeningHours
     */
    public function createFromArray(array $pickupPlaceOpeningHours): PickupPlaceOpeningHours
    {
        $closingTimesIndexedByDayOfWeek = [];

        foreach ($pickupPlaceOpeningHours['openingHoursOfDays'] as $openingHoursOfDay) {
            $closingTimesIndexedByDayOfWeek[$openingHoursOfDay['dayOfWeek']] = [
                ...$closingTimesIndexedByDayOfWeek[$openingHoursOfDay['dayOfWeek']] ?? [],
                ...$this->getClosingTimes($openingHoursOfDay['openingHoursRanges']),
            ];
        }

        $exceptionDays = [];

        foreach ($pickupPlaceOpeningHours['exceptionDays'] as $exceptionDay) {
            $exceptionDays[] = [
                'from' => $exceptionDay['from'],
                'to' => $exceptionDay['to'] ?? $exceptionDay['from'],
                'closingTimes' => $this->getClosingTimes($exceptionDay['openingHoursRanges']),
            ];
        }

        return $this->createInstance($closingTimesIndexedByDayOfWeek, $exceptionDays);
    }

    /**
     * @param array<int, string[]> $closingTimesIndexedByDayOfWeek
     * @param array<int, array{from: string, to: string, closingTimes: string[]}> $exceptionDays
     */
    protected function createInstance(
        array $closingTimesIndexedByDayOfWeek,
        array $exceptionDays,
    ): PickupPlaceOpeningHours {
        return new PickupPlaceOpeningHours($closingTimesIndexedByDayOfWeek, $exceptionDays);
    }

    /**
     * @param array<int, array{openingTime: string, closingTime: string}> $openingHoursRanges
     * @return string[]
     */
    protected function getClosingTimes(array $openingHoursRanges): array
    {
        return array_column($openingHoursRanges, 'closingTime');
    }
}
