<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use DateTimeImmutable;

class PacketeryPickupPointOpeningHoursProvider
{
    public function isOpenOnDate(PacketeryPickupPoint $packeteryPickupPoint, DateTimeImmutable $date): bool
    {
        return $this->getOpeningHoursRangesOnDate($packeteryPickupPoint, $date) !== [];
    }

    /**
     * @param string $time "HH:MM"
     */
    public function isOpenAfterTimeOnDate(
        PacketeryPickupPoint $packeteryPickupPoint,
        DateTimeImmutable $date,
        string $time,
    ): bool {
        return array_any(
            $this->getOpeningHoursRangesOnDate($packeteryPickupPoint, $date),
            static fn (string $openingHoursRange): bool => explode('-', $openingHoursRange)[1] > $time,
        );
    }

    /**
     * An exception for the date takes precedence over the upcoming opening hours, which take precedence over the regular ones
     *
     * @return string[]
     */
    protected function getOpeningHoursRangesOnDate(
        PacketeryPickupPoint $packeteryPickupPoint,
        DateTimeImmutable $date,
    ): array {
        $exceptionOpeningHours = $packeteryPickupPoint->getExceptionOpeningHours();
        $formattedDate = $date->format('Y-m-d');

        if (array_key_exists($formattedDate, $exceptionOpeningHours)) {
            return $exceptionOpeningHours[$formattedDate];
        }

        $upcomingOpeningHoursSince = $packeteryPickupPoint->getUpcomingOpeningHoursSince();
        $weekOpeningHours = $upcomingOpeningHoursSince !== null && $upcomingOpeningHoursSince->format('Y-m-d') <= $formattedDate
            ? $packeteryPickupPoint->getUpcomingOpeningHours() ?? []
            : $packeteryPickupPoint->getRegularOpeningHours();

        return $weekOpeningHours[(int)$date->format('N')] ?? [];
    }
}
