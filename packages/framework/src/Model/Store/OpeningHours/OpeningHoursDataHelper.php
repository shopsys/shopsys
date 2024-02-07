<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

class OpeningHoursDataHelper
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[] $openingHoursData
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[][]
     */
    public static function getOpeningHoursIndexedByDayNumber(array $openingHoursData): array
    {
        $openingHoursIndexedByDayNumber = [];

        foreach ($openingHoursData as $openingHourData) {
            $openingHoursIndexedByDayNumber[$openingHourData->dayOfWeek][] = $openingHourData;
        }

        return $openingHoursIndexedByDayNumber;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[][] $openingHoursDataIndexedByDayOfWeek
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public static function flattenOpeningHoursData(array $openingHoursDataIndexedByDayOfWeek): array
    {
        $flattenedOpeningHours = [];

        foreach ($openingHoursDataIndexedByDayOfWeek as $dayOfWeek => $openingHoursData) {
            foreach ($openingHoursData as $openingHours) {
                $openingHours->dayOfWeek = $dayOfWeek;
                $flattenedOpeningHours[] = $openingHours;
            }
        }

        return $flattenedOpeningHours;
    }
}
