<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

class OpeningHoursDataFactory
{
    public function __construct(
        protected readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
    ) {
    }

    public function createForDayOfWeek(int $dayOfWeek): OpeningHoursData
    {
        $openingHoursData = new OpeningHoursData();
        $openingHoursData->dayOfWeek = $dayOfWeek;

        return $openingHoursData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
     */
    public function createWeek(): array
    {
        $weekOpeningHourData = [];

        for ($i = 1; $i <= 7; $i++) {
            $weekOpeningHourData[] = $this->createForDayOfWeek($i);
        }

        return $weekOpeningHourData;
    }

    public function createFromOpeningHour(OpeningHours $openingHours): OpeningHoursData
    {
        $openingHoursData = $this->createForDayOfWeek($openingHours->getDayOfWeek());
        $openingHoursData->openingHoursRanges = $this->openingHoursRangeDataFactory->createOpeningHoursRangesDataFromEntities($openingHours->getOpeningHoursRanges());

        return $openingHoursData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours[] $openingHours
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData[]
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
