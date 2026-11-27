<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport\PickupPlace;

use DateTimeImmutable;

class PickupPlaceOpeningHoursProvider
{
    protected const string DATE_FORMAT = 'Y-m-d';

    /**
     * @param array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>} $pickupPlaceOpeningHours
     */
    public function isPickupPlaceOpenOnDate(array $pickupPlaceOpeningHours, DateTimeImmutable $date): bool
    {
        return $this->getOpeningHoursRangesOnDate($pickupPlaceOpeningHours, $date) !== [];
    }

    /**
     * @param array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>} $pickupPlaceOpeningHours
     */
    public function isPickupPlaceOpenAfterTimeOnDate(
        array $pickupPlaceOpeningHours,
        DateTimeImmutable $date,
        string $time,
    ): bool {
        return array_any(
            $this->getOpeningHoursRangesOnDate($pickupPlaceOpeningHours, $date),
            static fn (array $openingHoursRange): bool => $openingHoursRange['closingTime'] > $time,
        );
    }

    /**
     * @param array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>} $pickupPlaceOpeningHours
     * @return array<int, array{openingTime: string, closingTime: string}>
     */
    protected function getOpeningHoursRangesOnDate(array $pickupPlaceOpeningHours, DateTimeImmutable $date): array
    {
        $formattedDate = $date->format(static::DATE_FORMAT);

        foreach ($pickupPlaceOpeningHours['exceptionDays'] as $exceptionDay) {
            if ($exceptionDay['from'] <= $formattedDate && $formattedDate <= ($exceptionDay['to'] ?? $exceptionDay['from'])) {
                return $exceptionDay['openingHoursRanges'];
            }
        }

        $dayOfWeek = (int)$date->format('N');
        $openingHoursRanges = [];

        foreach ($pickupPlaceOpeningHours['openingHoursOfDays'] as $openingHoursOfDay) {
            if ($openingHoursOfDay['dayOfWeek'] === $dayOfWeek) {
                $openingHoursRanges = [...$openingHoursRanges, ...$openingHoursOfDay['openingHoursRanges']];
            }
        }

        return $openingHoursRanges;
    }
}
