<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\Status;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ComplaintStatusDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    protected function createInstance(): ComplaintStatusData
    {
        return new ComplaintStatusData();
    }

    public function create(): ComplaintStatusData
    {
        $complaintStatusData = $this->createInstance();
        $this->fillNew($complaintStatusData);

        return $complaintStatusData;
    }

    protected function fillNew(ComplaintStatusData $complaintStatusData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $complaintStatusData->name[$locale] = null;
        }
    }

    public function createFromComplaintStatus(ComplaintStatus $complaintStatus): ComplaintStatusData
    {
        $complaintStatusData = $this->createInstance();
        $this->fillFromComplaintStatus($complaintStatusData, $complaintStatus);

        return $complaintStatusData;
    }

    protected function fillFromComplaintStatus(
        ComplaintStatusData $complaintStatusData,
        ComplaintStatus $complaintStatus,
    ): void {
        $translations = $complaintStatus->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $complaintStatusData->name = $names;
    }
}
