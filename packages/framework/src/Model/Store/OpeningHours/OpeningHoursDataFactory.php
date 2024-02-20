<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

class OpeningHoursDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeDataFactory $openingHoursRangeDataFactory
     */
    public function __construct(
        protected readonly OpeningHoursRangeDataFactory $openingHoursRangeDataFactory,
    ) {
    }

    /**
     * @param int $dayOfWeek
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData
     */
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
