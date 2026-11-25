<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use DateTimeImmutable;

class PacketeryPickupPointFeedParser
{
    protected const array DAY_NAMES_TO_DAY_OF_WEEK_MAP = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    protected const string DATE_FORMAT = 'Y-m-d';

    /**
     * @param array<string, mixed> $feedItem a single point of the Packeta v5 branch or box feed
     */
    public function parseFeedItem(array $feedItem): PacketeryPickupPoint
    {
        $openingHours = $feedItem['openingHours'] ?? [];
        $upcomingOpeningHours = $openingHours['upcoming'] ?? null;
        $upcomingOpeningHoursSince = is_array($upcomingOpeningHours) ? $this->parseDate($upcomingOpeningHours['startDate'] ?? null) : null;

        return new PacketeryPickupPoint(
            (string)$feedItem['id'],
            $this->parseWeekOpeningHours($openingHours['regular'] ?? []),
            $upcomingOpeningHoursSince === null ? null : $this->parseWeekOpeningHours($upcomingOpeningHours),
            $upcomingOpeningHoursSince,
            $this->parseExceptionOpeningHours($openingHours['exceptions'] ?? []),
        );
    }

    /**
     * @param array<string, mixed> $weekOpeningHours
     * @return array<int, string[]>
     */
    protected function parseWeekOpeningHours(array $weekOpeningHours): array
    {
        $openingHoursIndexedByDayOfWeek = [];

        foreach (static::DAY_NAMES_TO_DAY_OF_WEEK_MAP as $dayName => $dayOfWeek) {
            $openingHoursRanges = $this->parseOpeningHoursRanges($weekOpeningHours[$dayName] ?? '');

            if ($openingHoursRanges !== []) {
                $openingHoursIndexedByDayOfWeek[$dayOfWeek] = $openingHoursRanges;
            }
        }

        return $openingHoursIndexedByDayOfWeek;
    }

    /**
     * The feed is converted from XML, so a single exception comes as {"exception": {...}}
     * while more of them come as {"exception": [{...}, ...]}
     *
     * @return array<string, string[]>
     */
    protected function parseExceptionOpeningHours(mixed $exceptions): array
    {
        $exceptionItems = is_array($exceptions) ? ($exceptions['exception'] ?? $exceptions) : [];

        if (isset($exceptionItems['date'])) {
            $exceptionItems = [$exceptionItems];
        }

        $openingHoursIndexedByDate = [];

        foreach ($exceptionItems as $exceptionItem) {
            $date = $this->parseDate($exceptionItem['date'] ?? null);

            if ($date !== null) {
                $openingHoursIndexedByDate[$date] = $this->parseOpeningHoursRanges($exceptionItem['hours'] ?? '');
            }
        }

        return $openingHoursIndexedByDate;
    }

    /**
     * Ranges come as "08:00–12:30, 13:30–17:30" separated by an en dash, sometimes by a hyphen;
     * a closed day comes as an empty string or as an empty object
     *
     * @return string[]
     */
    protected function parseOpeningHoursRanges(mixed $hours): array
    {
        if (!is_string($hours)) {
            return [];
        }

        $openingHoursRanges = [];

        foreach (explode(',', $hours) as $range) {
            $range = str_replace(['–', '—'], '-', trim($range));

            if (preg_match('/^\d{2}:\d{2}-\d{2}:\d{2}$/', $range) === 1) {
                $openingHoursRanges[] = $range;
            }
        }

        return $openingHoursRanges;
    }

    /**
     * @return string|null the date normalized to the "Y-m-d" format
     */
    protected function parseDate(mixed $date): ?string
    {
        if (!is_string($date)) {
            return null;
        }

        $parsedDate = DateTimeImmutable::createFromFormat('!' . static::DATE_FORMAT, $date);

        return $parsedDate === false ? null : $parsedDate->format(static::DATE_FORMAT);
    }
}
