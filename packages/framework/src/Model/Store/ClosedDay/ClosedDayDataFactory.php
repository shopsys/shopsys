<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

class ClosedDayDataFactory
{
    public function create(): ClosedDayData
    {
        return new ClosedDayData();
    }

    public function createFromClosedDay(ClosedDay $closedDay): ClosedDayData
    {
        $closedDayData = $this->create();

        $closedDayData->excludedStores = $closedDay->getExcludedStores();
        $closedDayData->domainId = $closedDay->getDomainId();
        $closedDayData->date = $closedDay->getDate();
        $closedDayData->name = $closedDay->getName();
        $closedDayData->isPublicHoliday = $closedDay->isPublicHoliday();

        return $closedDayData;
    }
}
