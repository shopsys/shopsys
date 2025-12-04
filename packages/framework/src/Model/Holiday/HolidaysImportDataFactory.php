<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Holiday;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class HolidaysImportDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportData
     */
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportData
     */
    protected function createInstance(): HolidaysImportData
    {
        return new HolidaysImportData();
    }
}
