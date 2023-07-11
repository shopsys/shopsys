<?php

declare(strict_types=1);

namespace App\Model\Store\OpeningHours;

class OpeningHoursDataFactory
{
    /**
     * @return \App\Model\Store\OpeningHours\OpeningHoursData
     */
    public function create(): OpeningHoursData
    {
        return new OpeningHoursData();
    }

    /**
     * @return \App\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public function createWeek(): array
    {
        $weekOpeningHourData = [];

        for ($i = 1; $i <= 7; $i++) {
            $openingHourData = $this->create();
            $openingHourData->dayOfWeek = $i;

            $weekOpeningHourData[] = $openingHourData;
        }

        return $weekOpeningHourData;
    }

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHours $openingHours
     * @return \App\Model\Store\OpeningHours\OpeningHoursData
     */
    public function createFromOpeningHour(OpeningHours $openingHours): OpeningHoursData
    {
        $openingHourData = $this->create();

        $openingHourData->dayOfWeek = $openingHours->getDayOfWeek();
        $openingHourData->firstOpeningTime = $openingHours->getFirstOpeningTime();
        $openingHourData->firstClosingTime = $openingHours->getFirstClosingTime();
        $openingHourData->secondOpeningTime = $openingHours->getSecondOpeningTime();
        $openingHourData->secondClosingTime = $openingHours->getSecondClosingTime();

        return $openingHourData;
    }

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHours[] $openingHours
     * @return \App\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public function createWholeWeekOpeningHours(array $openingHours): array
    {
        $openingHoursData = [];

        foreach ($openingHours as $openingHour) {
            $openingHoursData[] = $this->createFromOpeningHour($openingHour);
        }

        return $openingHoursData;
    }
}
