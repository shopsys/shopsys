<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Holiday;

use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Spatie\Holidays\Holidays;

class HolidaysImportFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayDataFactory $closedDayDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade $closedDayFacade
     */
    public function __construct(
        protected readonly ClosedDayDataFactory $closedDayDataFactory,
        protected readonly ClosedDayFacade $closedDayFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportData $holidaysImportData
     * @return int
     */
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

                $this->closedDayFacade->create($closedDayData);
                $importedHolidays++;
            }
        }

        return $importedHolidays;
    }
}
