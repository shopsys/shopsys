<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

class OpeningHoursRangeDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange[] $openingHoursRanges
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData[]
     */
    public function createOpeningHoursRangesDataFromEntities(array $openingHoursRanges): array
    {
        $openingHoursRangesData = [];

        foreach ($openingHoursRanges as $openingHoursRange) {
            $openingHoursRangesData[] = $this->create(
                $openingHoursRange->getOpeningTime(),
                $openingHoursRange->getClosingTime(),
            );
        }

        return $openingHoursRangesData;
    }

    /**
     * @param string $openingTime
     * @param string $closingTime
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData
     */
    public function create(string $openingTime, string $closingTime): OpeningHoursRangeData
    {
        $openingHoursRangeData = $this->createInstance();
        $openingHoursRangeData->openingTime = $openingTime;
        $openingHoursRangeData->closingTime = $closingTime;

        return $openingHoursRangeData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData
     */
    protected function createInstance(): OpeningHoursRangeData
    {
        return new OpeningHoursRangeData();
    }
}
