<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use DateTimeImmutable;

/**
 * Opening hours of a Packeta pickup point (a branch or a Z-BOX) imported from the Packeta pickup point feeds,
 * so the expected delivery date can respect the point selected in the cart;
 * every opening hours range is a "HH:MM-HH:MM" string and every date is a "Y-m-d" string
 */
class PacketeryPickupPoint
{
    /**
     * @param array<int, string[]> $regularOpeningHours ranges indexed by the ISO-8601 day of week, a missing day means closed
     * @param array<int, string[]>|null $upcomingOpeningHours new regular opening hours valid since $upcomingOpeningHoursSince
     * @param array<string, string[]> $exceptionOpeningHours ranges indexed by the date they override, an empty list means closed
     */
    public function __construct(
        public readonly string $id,
        public readonly array $regularOpeningHours,
        public readonly ?array $upcomingOpeningHours,
        public readonly ?string $upcomingOpeningHoursSince,
        public readonly array $exceptionOpeningHours,
    ) {
    }

    public function isOpenOnDate(DateTimeImmutable $date): bool
    {
        return $this->getOpeningHoursRangesOnDate($date) !== [];
    }

    /**
     * @param string $time "HH:MM"
     */
    public function isOpenAfterTimeOnDate(DateTimeImmutable $date, string $time): bool
    {
        return array_any(
            $this->getOpeningHoursRangesOnDate($date),
            static fn (string $openingHoursRange): bool => explode('-', $openingHoursRange)[1] > $time,
        );
    }

    /**
     * An exception for the date takes precedence over the upcoming opening hours, which take precedence over the regular ones
     *
     * @return string[]
     */
    protected function getOpeningHoursRangesOnDate(DateTimeImmutable $date): array
    {
        $formattedDate = $date->format('Y-m-d');

        if (array_key_exists($formattedDate, $this->exceptionOpeningHours)) {
            return $this->exceptionOpeningHours[$formattedDate];
        }

        $weekOpeningHours = $this->upcomingOpeningHoursSince !== null && $this->upcomingOpeningHoursSince <= $formattedDate
            ? $this->upcomingOpeningHours ?? []
            : $this->regularOpeningHours;

        return $weekOpeningHours[(int)$date->format('N')] ?? [];
    }
}
