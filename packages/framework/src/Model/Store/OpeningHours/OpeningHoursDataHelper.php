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
}
