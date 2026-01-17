<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Holiday;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class HolidaysImportDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    public function create(): HolidaysImportData
    {
        $holidaysImportData = $this->createInstance();
        $holidaysImportData->year = (int)date('Y');

        if ($this->domain->isMultidomain() === false) {
            $holidaysImportData->selectedDomains = [
                Domain::FIRST_DOMAIN_ID => true,
            ];
        }

        return $holidaysImportData;
    }

    protected function createInstance(): HolidaysImportData
    {
        return new HolidaysImportData();
    }
}
