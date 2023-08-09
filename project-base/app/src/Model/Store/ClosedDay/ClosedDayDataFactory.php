<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay;

class ClosedDayDataFactory
{
    /**
     * @return \App\Model\Store\ClosedDay\ClosedDayData
     */
    public function create(): ClosedDayData
    {
        return new ClosedDayData();
    }

    /**
     * @param \App\Model\Store\ClosedDay\ClosedDay $closedDay
     * @return \App\Model\Store\ClosedDay\ClosedDayData
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
