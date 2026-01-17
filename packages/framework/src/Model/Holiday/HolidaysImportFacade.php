<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Holiday;

use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Spatie\Holidays\Holidays;

class HolidaysImportFacade
{
    public function __construct(
        protected readonly ClosedDayDataFactory $closedDayDataFactory,
        protected readonly ClosedDayFacade $closedDayFacade,
    ) {
    }

    public function import(HolidaysImportData $holidaysImportData): int
    {
        $importedHolidays = 0;

        foreach ($holidaysImportData->selectedDomains as $domainId => $isSelected) {
            if ($isSelected === false) {
                continue;
            }

            $holidays = Holidays::for($holidaysImportData->country->getCode(), $holidaysImportData->year)->get();

            foreach ($holidays as $holiday) {
                $closedDayData = $this->closedDayDataFactory->create();

                $closedDayData->domainId = $domainId;
                $closedDayData->date = $holiday['date'];
                $closedDayData->name = $holiday['name'];
                $closedDayData->isPublicHoliday = true;

                $this->closedDayFacade->create($closedDayData);
                $importedHolidays++;
            }
        }

        return $importedHolidays;
    }
}
