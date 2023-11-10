<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

class ClosedDayDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData
     */
    public function create(): ClosedDayData
    {
        return new ClosedDayData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay $closedDay
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData
     */
    public function createFromClosedDay(ClosedDay $closedDay): ClosedDayData
    {
        $closedDayData = $this->create();

        $closedDayData->excludedStores = $closedDay->getExcludedStores();
        $closedDayData->domainId = $closedDay->getDomainId();
        $closedDayData->date = $closedDay->getDate();
        $closedDayData->name = $closedDay->getName();

        return $closedDayData;
    }
}
